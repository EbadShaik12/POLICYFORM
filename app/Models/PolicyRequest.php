<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PolicyRequest extends Model
{
    protected $fillable = [
        'client_request_id',
        'agent_id',
        'customer_name',
        'date_of_birth',
        'destination',
        'trip_start',
        'trip_end',
        'plan_code',
        'sum_insured',
        'premium',
        'status',
        'policy_number',
        'provider_reference',
        'last_response',
        'attempt_count',
    ];

    protected function casts(): array
    {
        return [
            'date_of_birth' => 'date',
            'trip_start' => 'date',
            'trip_end' => 'date',
            'premium' => 'decimal:2',
        ];
    }
}