<?php

namespace Tests\Feature;

use App\Models\PolicyRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PolicyRequestApiTest extends TestCase
{
    use RefreshDatabase;

    private array $validPayload = [
        'client_request_id' => 'REQ-TEST-1001',
        'agent_id' => 'AGENT-42',
        'customer_name' => 'John Doe',
        'date_of_birth' => '1995-06-15',
        'destination' => 'INTERNATIONAL',
        'trip_start' => '2026-11-10',
        'trip_end' => '2026-11-19', // 10 days
        'plan_code' => 'PLUS',      // 20
        'sum_insured' => 300000,    // 1.25 multiplier
    ];

    /**
     * Test /api/calculate-premium endpoint returns correct calculation.
     */
    public function test_calculate_premium_endpoint(): void
    {
        $response = $this->postJson('/api/calculate-premium', [
            'date_of_birth' => '1995-06-15',
            'destination' => 'DOMESTIC',
            'trip_start' => '2026-11-01',
            'trip_end' => '2026-11-05', // 5 days
            'plan_code' => 'BASIC',     // 12
            'sum_insured' => 100000,    // 1.00
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'premium' => 60.00,
                'days' => 5,
                'base_rate' => 12,
                'age_multiplier' => 1.00,
                'destination_multiplier' => 1.00,
                'coverage_multiplier' => 1.00,
            ]);
    }

    /**
     * Test validation failure when trip_end is before trip_start.
     */
    public function test_validation_fails_on_invalid_dates(): void
    {
        $payload = $this->validPayload;
        $payload['trip_end'] = '2026-11-01'; // Before start (2026-11-10)

        $response = $this->postJson('/api/policy-requests', $payload);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['trip_end']);
    }

    /**
     * Test validation failure on invalid plan code.
     */
    public function test_validation_fails_on_invalid_plan(): void
    {
        $payload = $this->validPayload;
        $payload['plan_code'] = 'SUPER_VIP';

        $response = $this->postJson('/api/policy-requests', $payload);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['plan_code']);
    }

    /**
     * Test successful creation of policy request returns HTTP 201.
     */
    public function test_creates_policy_request_successfully(): void
    {
        $response = $this->postJson('/api/policy-requests', $this->validPayload);

        $response->assertStatus(201)
            ->assertJsonFragment([
                'client_request_id' => 'REQ-TEST-1001',
                'agent_id' => 'AGENT-42',
                'status' => 'VALIDATED',
            ]);

        $this->assertDatabaseHas('policy_requests', [
            'client_request_id' => 'REQ-TEST-1001',
            'agent_id' => 'AGENT-42',
            'status' => 'VALIDATED',
        ]);
    }

    /**
     * Test duplicate request prevention: identical agent_id and client_request_id returns 200 and same record.
     */
    public function test_duplicate_submission_returns_existing_record_with_200(): void
    {
        // First submission -> 201
        $res1 = $this->postJson('/api/policy-requests', $this->validPayload);
        $res1->assertStatus(201);
        $id1 = $res1->json('id');

        // Repeated submission with same agent and client_request_id -> 200
        $res2 = $this->postJson('/api/policy-requests', $this->validPayload);
        $res2->assertStatus(200);
        $this->assertEquals($id1, $res2->json('id'));

        // Verify only 1 record exists in database
        $this->assertEquals(1, PolicyRequest::where('agent_id', 'AGENT-42')->count());
    }

    /**
     * Test retrieval via show endpoint by database ID and client_request_id.
     */
    public function test_show_policy_request(): void
    {
        $policy = PolicyRequest::create(array_merge($this->validPayload, [
            'premium' => 450.00,
            'status' => 'VALIDATED',
        ]));

        // By numeric ID
        $res1 = $this->getJson("/api/policy-requests/{$policy->id}");
        $res1->assertStatus(200)->assertJson(['id' => $policy->id]);

        // By client_request_id
        $res2 = $this->getJson("/api/policy-requests/{$policy->client_request_id}");
        $res2->assertStatus(200)->assertJson(['id' => $policy->id]);

        // Non-existent ID -> 404
        $res3 = $this->getJson('/api/policy-requests/NON-EXISTENT-ID');
        $res3->assertStatus(404);
    }

    /**
     * Test list endpoint with pagination and status filtering.
     */
    public function test_index_policy_requests_with_filtering(): void
    {
        PolicyRequest::create(array_merge($this->validPayload, [
            'client_request_id' => 'REQ-1',
            'status' => 'VALIDATED',
            'premium' => 100,
        ]));
        PolicyRequest::create(array_merge($this->validPayload, [
            'client_request_id' => 'REQ-2',
            'status' => 'ISSUED',
            'premium' => 200,
        ]));

        $resAll = $this->getJson('/api/policy-requests');
        $resAll->assertStatus(200)->assertJsonCount(2, 'data');

        $resFiltered = $this->getJson('/api/policy-requests?status=ISSUED');
        $resFiltered->assertStatus(200)->assertJsonCount(1, 'data');
    }

    /**
     * Test submitting policy for issuance transitions to PENDING and generates provider_reference.
     */
    public function test_submit_for_issuance(): void
    {
        $policy = PolicyRequest::create(array_merge($this->validPayload, [
            'premium' => 120.00,
            'status' => 'VALIDATED',
        ]));

        $res = $this->postJson("/api/policy-requests/{$policy->id}/submit");

        $res->assertStatus(200)
            ->assertJsonPath('policy.status', 'PENDING');

        $this->assertDatabaseHas('policy_requests', [
            'id' => $policy->id,
            'status' => 'PENDING',
            'attempt_count' => 1,
        ]);
        $this->assertNotNull($res->json('policy.provider_reference'));
    }

    /**
     * Test mock insurer callback successfully issues policy.
     */
    public function test_mock_insurer_callback_issues_policy(): void
    {
        $policy = PolicyRequest::create(array_merge($this->validPayload, [
            'premium' => 120.00,
            'status' => 'PENDING',
            'provider_reference' => 'INS-REF-TEST999',
            'attempt_count' => 1,
        ]));

        $callbackRes = $this->postJson('/api/mock-insurer/callback', [
            'provider_reference' => 'INS-REF-TEST999',
            'status' => 'ISSUED',
            'policy_number' => 'POL-CUSTOM-12345',
        ]);

        $callbackRes->assertStatus(200)
            ->assertJsonPath('policy.status', 'ISSUED')
            ->assertJsonPath('policy.policy_number', 'POL-CUSTOM-12345');

        $this->assertDatabaseHas('policy_requests', [
            'id' => $policy->id,
            'status' => 'ISSUED',
            'policy_number' => 'POL-CUSTOM-12345',
        ]);
    }

    /**
     * Test that an ISSUED policy cannot be overwritten or issued again by repeated submit or callback.
     */
    public function test_issued_policy_cannot_be_overwritten(): void
    {
        $policy = PolicyRequest::create(array_merge($this->validPayload, [
            'premium' => 120.00,
            'status' => 'ISSUED',
            'policy_number' => 'POL-ORIGINAL-777',
            'provider_reference' => 'INS-REF-FINAL',
        ]));

        // Attempting to submit an already ISSUED policy
        $submitRes = $this->postJson("/api/policy-requests/{$policy->id}/submit");
        $submitRes->assertStatus(200)
            ->assertJsonPath('policy.status', 'ISSUED');

        // Attempting a callback with FAILED status on an already ISSUED policy
        $callbackRes = $this->postJson('/api/mock-insurer/callback', [
            'provider_reference' => 'INS-REF-FINAL',
            'status' => 'FAILED',
            'error_message' => 'Should not overwrite issued state',
        ]);

        $callbackRes->assertStatus(200);

        // Verify status and policy_number are still untouched
        $this->assertDatabaseHas('policy_requests', [
            'id' => $policy->id,
            'status' => 'ISSUED',
            'policy_number' => 'POL-ORIGINAL-777',
        ]);
    }

    /**
     * Test mock insurer callback handling rejection (FAILED).
     */
    public function test_mock_insurer_callback_rejection(): void
    {
        $policy = PolicyRequest::create(array_merge($this->validPayload, [
            'premium' => 120.00,
            'status' => 'PENDING',
            'provider_reference' => 'INS-REF-FAIL01',
        ]));

        $res = $this->postJson('/api/mock-insurer/callback', [
            'provider_reference' => 'INS-REF-FAIL01',
            'status' => 'FAILED',
            'error_message' => 'Destination risk criteria exceeded',
        ]);

        $res->assertStatus(200)
            ->assertJsonPath('policy.status', 'FAILED');

        $this->assertDatabaseHas('policy_requests', [
            'id' => $policy->id,
            'status' => 'FAILED',
        ]);
    }

    /**
     * Test pure numeric application ID and agent ID creation and tracking lookup.
     */
    public function test_numeric_application_and_agent_id_submission_and_tracking(): void
    {
        $numericPayload = [
            'client_request_id' => '748291',
            'agent_id' => '1001',
            'customer_name' => 'Sara Connor',
            'date_of_birth' => '1998-07-21',
            'destination' => 'DOMESTIC',
            'trip_start' => '2026-12-05',
            'trip_end' => '2026-12-08',
            'plan_code' => 'BASIC',
            'sum_insured' => 100000,
        ];

        // 1. Submit numeric policy request
        $createRes = $this->postJson('/api/policy-requests', $numericPayload);
        $createRes->assertStatus(201)
            ->assertJsonPath('client_request_id', '748291')
            ->assertJsonPath('agent_id', '1001');

        // 2. Track using 6-digit numeric application ID
        $trackRes = $this->getJson('/api/policy-requests/748291');
        $trackRes->assertStatus(200)
            ->assertJsonPath('client_request_id', '748291')
            ->assertJsonPath('customer_name', 'Sara Connor');
    }
}

