<?php

namespace AgentNetwork\Livewire;

use AgentNetwork\AgentNetworkManager;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class Dashboard extends Component
{
    public array $stats              = [];
    public array $recentTransactions = [];
    public array $commissionBreakdown = [];
    public bool  $ready              = false;

    public function mount(): void
    {
        if (! AgentNetworkManager::isSetup()) return;

        try {
            $eTable  = AgentNetworkManager::table('entities');
            $txTable = AgentNetworkManager::table('transactions');
            $lTable  = AgentNetworkManager::table('ledgers');
            $eKey    = AgentNetworkManager::entityKey();

            $this->stats = [
                'total_entities'   => DB::table($eTable)->count(),
                'active_entities'  => DB::table($eTable)->where('status', 'active')->count(),
                'transactions_mtd' => DB::table($txTable)->whereMonth('created_at', now()->month)->whereYear('created_at', now()->year)->count(),
                'commissions_mtd'  => (float) DB::table($lTable)->whereMonth('created_at', now()->month)->whereYear('created_at', now()->year)->sum('amount'),
                'pending_payouts'  => (float) DB::table($lTable)->where('status', 'pending')->sum('amount'),
                'paid_mtd'         => (float) DB::table($lTable)->where('status', 'paid')->whereMonth('paid_at', now()->month)->whereYear('paid_at', now()->year)->sum('amount'),
            ];

            $this->recentTransactions = DB::table($txTable . ' as t')
                ->join($eTable . ' as e', "t.{$eKey}", '=', 'e.id')
                ->select('t.id', 't.amount', 't.created_at', 'e.name as entity_name', 'e.type as entity_type')
                ->orderByDesc('t.created_at')
                ->limit(8)
                ->get()
                ->map(fn ($r) => (array) $r)
                ->toArray();

            $this->commissionBreakdown = DB::table($lTable)
                ->select('commission_type', DB::raw('COUNT(*) as count'), DB::raw('SUM(amount) as total'))
                ->whereMonth('created_at', now()->month)
                ->whereYear('created_at', now()->year)
                ->groupBy('commission_type')
                ->get()
                ->map(fn ($r) => (array) $r)
                ->toArray();

            $this->ready = true;
        } catch (\Exception) {
            // tables not migrated yet
        }
    }

    public function render()
    {
        return view('agent-network::livewire.dashboard')
            ->layout('agent-network::layout', ['title' => 'Dashboard']);
    }
}
