<?php

namespace AgentNetwork\Livewire;

use AgentNetwork\AgentNetworkManager;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\WithPagination;

class PayoutQueue extends Component
{
    use WithPagination;

    public string $statusFilter = 'pending';
    public string $typeFilter   = '';
    public array  $summary      = [];
    public bool   $ready        = false;
    public array  $selected     = [];
    public bool   $selectAll    = false;

    public function updatingStatusFilter(): void { $this->resetPage(); }
    public function updatingTypeFilter(): void   { $this->resetPage(); }

    public function mount(): void
    {
        $this->loadSummary();
    }

    protected function loadSummary(): void
    {
        if (! AgentNetworkManager::isSetup()) return;

        try {
            $table = AgentNetworkManager::table('ledgers');

            $this->summary = [
                'pending_count'  => DB::table($table)->where('status', 'pending')->count(),
                'pending_amount' => (float) DB::table($table)->where('status', 'pending')->sum('amount'),
                'paid_count'     => DB::table($table)->where('status', 'paid')->count(),
                'paid_amount'    => (float) DB::table($table)->where('status', 'paid')->sum('amount'),
            ];

            $this->ready = true;
        } catch (\Exception) {}
    }

    public function markPaid(int $id): void
    {
        try {
            DB::table(AgentNetworkManager::table('ledgers'))
                ->where('id', $id)
                ->update(['status' => 'paid', 'paid_at' => now(), 'updated_at' => now()]);

            $this->loadSummary();
            $this->selected = array_values(array_filter($this->selected, fn ($i) => $i !== $id));

            Notification::make()->title('Marked as paid.')->success()->send();
        } catch (\Exception) {
            Notification::make()->title('Failed.')->danger()->send();
        }
    }

    public function markSelectedPaid(): void
    {
        if (empty($this->selected)) return;

        try {
            DB::table(AgentNetworkManager::table('ledgers'))
                ->whereIn('id', $this->selected)
                ->update(['status' => 'paid', 'paid_at' => now(), 'updated_at' => now()]);

            $count = count($this->selected);
            $this->selected   = [];
            $this->selectAll  = false;

            $this->loadSummary();

            Notification::make()->title("{$count} entries marked as paid.")->success()->send();
        } catch (\Exception) {
            Notification::make()->title('Failed.')->danger()->send();
        }
    }

    public function render()
    {
        $ledgers = null;

        if (AgentNetworkManager::isSetup()) {
            try {
                $lTable = AgentNetworkManager::table('ledgers');
                $eTable = AgentNetworkManager::table('entities');
                $eKey   = AgentNetworkManager::entityKey();

                $query = DB::table($lTable . ' as l')
                    ->join($eTable . ' as e', "l.{$eKey}", '=', 'e.id')
                    ->select('l.id', 'l.commission_type', 'l.level', 'l.amount', 'l.status', 'l.paid_at', 'l.created_at', 'e.name as entity_name', 'e.type as entity_type');

                if ($this->statusFilter) {
                    $query->where('l.status', $this->statusFilter);
                }
                if ($this->typeFilter) {
                    $query->where('l.commission_type', $this->typeFilter);
                }

                $ledgers = $query->orderByDesc('l.created_at')->paginate(25);
            } catch (\Exception) {}
        }

        return view('agent-network::livewire.payout-queue', [
            'ledgers' => $ledgers,
        ])->layout('agent-network::layout', ['title' => 'Payout Queue']);
    }
}
