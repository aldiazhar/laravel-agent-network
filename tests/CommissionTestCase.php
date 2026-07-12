<?php

namespace AgentNetwork\Tests;

use App\Events\AgentNetwork\TransactionRegistered;
use App\Models\AgentNetwork\Agent;
use App\Models\AgentNetwork\CommissionLedger;
use App\Models\AgentNetwork\CommissionRule;
use App\Models\AgentNetwork\Transaction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

abstract class CommissionTestCase extends TestCase
{
    use RefreshDatabase;

    protected function makeAgent(string $name, string $type, ?Agent $parent = null): Agent
    {
        return Agent::create([
            'name'      => $name,
            'type'      => $type,
            'parent_id' => $parent?->id,
            'status'    => 'active',
        ]);
    }

    protected function makeRule(string $commissionType, array $conditions, ?string $entityType = null): CommissionRule
    {
        return CommissionRule::create([
            'commission_type' => $commissionType,
            'entity_type'     => $entityType,
            'active'          => true,
            'conditions'      => $conditions,
        ]);
    }

    protected function fire(Agent $agent, float $amount): void
    {
        $transaction = Transaction::create([
            'agent_id' => $agent->id,
            'amount'   => $amount,
        ]);

        TransactionRegistered::dispatch($agent, $transaction);
    }

    protected function totalFor(Agent $agent, string $type): float
    {
        return (float) CommissionLedger::where('agent_id', $agent->id)
            ->where('commission_type', $type)
            ->sum('amount');
    }
}
