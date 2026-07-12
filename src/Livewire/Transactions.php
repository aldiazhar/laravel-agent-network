<?php

namespace AgentNetwork\Livewire;

use AgentNetwork\AgentNetworkManager;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\WithPagination;

class Transactions extends Component
{
    use WithPagination;

    public string $search    = '';
    public string $dateFrom  = '';
    public string $dateTo    = '';
    public bool   $ready     = false;

    public function updatingSearch(): void  { $this->resetPage(); }
    public function updatingDateFrom(): void { $this->resetPage(); }
    public function updatingDateTo(): void   { $this->resetPage(); }

    public function render()
    {
        $transactions = null;
        $total        = 0;

        if (AgentNetworkManager::isSetup()) {
            try {
                $eTable  = AgentNetworkManager::table('entities');
                $txTable = AgentNetworkManager::table('transactions');
                $eKey    = AgentNetworkManager::entityKey();

                $query = DB::table($txTable . ' as t')
                    ->join($eTable . ' as e', "t.{$eKey}", '=', 'e.id')
                    ->select('t.id', 't.amount', 't.created_at', 'e.name as entity_name', 'e.type as entity_type', "t.{$eKey}");

                if ($this->search) {
                    $query->where('e.name', 'like', '%' . $this->search . '%');
                }
                if ($this->dateFrom) {
                    $query->whereDate('t.created_at', '>=', $this->dateFrom);
                }
                if ($this->dateTo) {
                    $query->whereDate('t.created_at', '<=', $this->dateTo);
                }

                $total        = $query->sum('t.amount');
                $transactions = $query->orderByDesc('t.created_at')->paginate(20);
                $this->ready  = true;
            } catch (\Exception) {}
        }

        return view('agent-network::livewire.transactions', [
            'transactions' => $transactions,
            'total'        => $total,
        ])->layout('agent-network::layout', ['title' => 'Transactions']);
    }
}
