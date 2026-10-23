<?php

namespace App\Http\Controllers;

use App\Models\PolicyRequest;
use App\Services\PremiumCalculator;
use Carbon\Carbon;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PolicyRequestController extends Controller
{
    /**
     * List policy requests with pagination and optional status filter.
     */
    public function index(Request $request): JsonResponse
    {
        $query = PolicyRequest::query();

        if ($request->filled('status')) {
            $query->where('status', strtoupper($request->status));
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('client_request_id', 'like', "%{$search}%")
                    ->orWhere('customer_name', 'like', "%{$search}%")
                    ->orWhere('agent_id', 'like', "%{$search}%")
                    ->orWhere('policy_number', 'like', "%{$search}%");
            });
        }

        return response()->json(
            $query->latest()->paginate(10)
        );
    }

    /**
     * Calculate premium breakdown without persisting to the database.
     */
    public function calculatePremium(Request $request): JsonResponse
    {
        $data = $request->validate([
            'date_of_birth' => 'required|date|before_or_equal:trip_start',
            'destination' => 'required|in:DOMESTIC,INTERNATIONAL,Domestic,International',
            'trip_start' => 'required|date',
            'trip_end' => 'required|date|after_or_equal:trip_start',
            'plan_code' => 'required|in:BASIC,PLUS,PREMIUM,Basic,Plus,Premium',
            'sum_insured' => 'required|integer|in:100000,300000,500000',
        ]);

        $calculation = PremiumCalculator::calculate(
            planCode: $data['plan_code'],
            dateOfBirth: $data['date_of_birth'],
            tripStart: $data['trip_start'],
            tripEnd: $data['trip_end'],
            destination: $data['destination'],
            sumInsured: (int) $data['sum_insured']
        );

        return response()->json($calculation);
    }

    /**
     * Store a new policy request or return existing request if duplicate.
     */
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'client_request_id' => 'required|string|max:100',
            'agent_id' => 'required|string|max:100',
            'customer_name' => 'required|string|max:255',
            'date_of_birth' => 'required|date|before_or_equal:trip_start',
            'destination' => 'required|in:DOMESTIC,INTERNATIONAL,Domestic,International',
            'trip_start' => 'required|date',
            'trip_end' => 'required|date|after_or_equal:trip_start',
            'plan_code' => 'required|in:BASIC,PLUS,PREMIUM,Basic,Plus,Premium',
            'sum_insured' => 'required|integer|in:100000,300000,500000',
        ]);

        $data['destination'] = strtoupper($data['destination']);
        $data['plan_code'] = strtoupper($data['plan_code']);

        // Check for existing request with the same agent_id and client_request_id
        $existing = PolicyRequest::where('agent_id', $data['agent_id'])
            ->where('client_request_id', $data['client_request_id'])
            ->first();

        if ($existing) {
            return response()->json($existing, 200);
        }

        // Calculate verified premium
        $calculation = PremiumCalculator::calculate(
            planCode: $data['plan_code'],
            dateOfBirth: $data['date_of_birth'],
            tripStart: $data['trip_start'],
            tripEnd: $data['trip_end'],
            destination: $data['destination'],
            sumInsured: (int) $data['sum_insured']
        );

        $data['premium'] = $calculation['premium'];
        $data['status'] = 'VALIDATED';

        try {
            $policy = DB::transaction(
                fn () => PolicyRequest::create($data)
            );
        } catch (QueryException $e) {
            // Handle simultaneous race condition submissions safely.
            $existing = PolicyRequest::where('agent_id', $data['agent_id'])
                ->where('client_request_id', $data['client_request_id'])
                ->first();

            if ($existing) {
                return response()->json($existing, 200);
            }

            throw $e;
        }

        return response()->json($policy, 201);
    }

    /**
     * Retrieve a policy request by database ID or client_request_id.
     */
    public function show(string $id): JsonResponse
    {
        $policy = PolicyRequest::where('client_request_id', $id)->first();

        if (!$policy && is_numeric($id)) {
            $policy = PolicyRequest::find((int) $id);
        }

        if (!$policy) {
            return response()->json([
                'message' => 'Policy request not found',
            ], 404);
        }

        return response()->json($policy);
    }

    /**
     * Submit or resume policy request issuance.
     */
    public function submit(string $id): JsonResponse
    {
        $policy = PolicyRequest::where('client_request_id', $id)->first();
        if (!$policy && is_numeric($id)) {
            $policy = PolicyRequest::find((int) $id);
        }

        if (!$policy) {
            return response()->json([
                'message' => 'Policy request not found',
            ], 404);
        }

        // Idempotency: Do not allow repeated submissions to overwrite an ISSUED state
        if ($policy->status === 'ISSUED') {
            return response()->json([
                'message' => 'Policy has already been issued.',
                'policy' => $policy,
            ], 200);
        }

        // Transition to PENDING, generate provider reference if missing, increment attempt count
        $policy->attempt_count = ($policy->attempt_count ?? 0) + 1;
        if (empty($policy->provider_reference)) {
            $policy->provider_reference = 'INS-REF-' . strtoupper(Str::random(10));
        }
        $policy->status = 'PENDING';
        $policy->last_response = json_encode([
            'action' => 'SUBMITTED_TO_INSURER',
            'timestamp' => Carbon::now()->toIso8601String(),
            'attempt' => $policy->attempt_count,
        ]);
        $policy->save();

        return response()->json([
            'message' => 'Policy request submitted to insurer successfully.',
            'policy' => $policy,
        ], 200);
    }

    /**
     * Process simulated insurer webhook callback.
     */
    public function mockInsurerCallback(Request $request): JsonResponse
    {
        $data = $request->validate([
            'provider_reference' => 'required|string|max:100',
            'status' => 'required|in:ISSUED,FAILED',
            'policy_number' => 'nullable|string|max:100',
            'error_message' => 'nullable|string|max:500',
        ]);

        $policy = PolicyRequest::where('provider_reference', $data['provider_reference'])->first();

        if (!$policy) {
            return response()->json([
                'message' => 'No policy request found matching provider_reference: ' . $data['provider_reference'],
            ], 404);
        }

        // Strict reliability rule: Do not allow callbacks to overwrite a confirmed ISSUED state
        if ($policy->status === 'ISSUED') {
            return response()->json([
                'message' => 'Policy is already ISSUED. Confirmed state cannot be overwritten.',
                'policy' => $policy,
            ], 200);
        }

        if ($data['status'] === 'ISSUED') {
            $policy->status = 'ISSUED';
            $policy->policy_number = $data['policy_number'] ?? ('POL-' . date('Ymd') . '-' . strtoupper(Str::random(8)));
            $policy->last_response = json_encode([
                'event' => 'INSURER_ISSUED',
                'timestamp' => Carbon::now()->toIso8601String(),
                'provider_reference' => $data['provider_reference'],
                'policy_number' => $policy->policy_number,
            ]);
            $policy->save();

            return response()->json([
                'message' => 'Policy successfully issued by insurer.',
                'policy' => $policy,
            ], 200);
        }

        // Status is FAILED
        $policy->status = 'FAILED';
        $policy->last_response = json_encode([
            'event' => 'INSURER_REJECTED',
            'timestamp' => Carbon::now()->toIso8601String(),
            'provider_reference' => $data['provider_reference'],
            'error_message' => $data['error_message'] ?? 'Underwriting criteria not met.',
        ]);
        $policy->save();

        return response()->json([
            'message' => 'Policy issuance marked as failed per insurer response.',
            'policy' => $policy,
        ], 200);
    }
}
