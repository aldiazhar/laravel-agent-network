<?php

namespace AgentNetwork\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static void transact(int|string $actorId, int|float $amount, int|string $ref)
 */
class AgentNetwork extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'agent-network';
    }
}
