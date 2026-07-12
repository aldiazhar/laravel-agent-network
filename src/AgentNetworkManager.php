<?php

namespace AgentNetwork;

use Illuminate\Support\Str;

class AgentNetworkManager
{
    public function transact(int|string $actorId, int|float $amount, int|string $ref): void
    {
        $entityName  = config('agent-network.entity.name', 'Agent');
        $entityClass = "App\\Models\\AgentNetwork\\{$entityName}";

        $actor = $entityClass::findOrFail($actorId);

        $transaction = \App\Models\AgentNetwork\Transaction::create([
            'agent_id' => $actor->id,
            'amount'   => $amount,
            'ref_id'   => $ref,
        ]);

        \App\Events\AgentNetwork\TransactionRegistered::dispatch($actor, $transaction);
    }

    public static function isSetup(): bool
    {
        return config('agent-network') !== null;
    }

    public static function table(string $name): string
    {
        $prefix = config('agent-network.entity.prefix', 'an_');

        return match ($name) {
            'entities'     => config('agent-network.entity.table', $prefix . 'agents'),
            'rules'        => $prefix . 'commission_rules',
            'ledgers'      => $prefix . 'commission_ledgers',
            'transactions' => $prefix . 'transactions',
            default        => $prefix . $name,
        };
    }

    public static function entityKey(): string
    {
        return Str::snake(config('agent-network.entity.name', 'Agent')) . '_id';
    }

    public static function currency(): string
    {
        return config('agent-network.currency', 'IDR');
    }

    public static function formatAmount(float $amount): string
    {
        $currency  = static::currency();
        $formatted = number_format($amount, 0, ',', '.');

        return $currency === 'IDR' ? 'Rp ' . $formatted : $currency . ' ' . $formatted;
    }
}
