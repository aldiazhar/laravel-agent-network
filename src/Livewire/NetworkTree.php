<?php

namespace AgentNetwork\Livewire;

use AgentNetwork\AgentNetworkManager;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Livewire\Component;

class NetworkTree extends Component
{
    public array  $allNodes    = [];
    public array  $roots       = [];
    public array  $expanded    = [];
    public array  $stats       = [];
    public string $search      = '';
    public array  $entityTypes = [];

    public bool   $showForm  = false;
    public string $formMode  = 'create';
    public array  $form      = [];

    public function mount(): void
    {
        $this->loadTree();
        $this->loadEntityTypes();
        $this->resetForm();
    }

    private function loadTree(): void
    {
        try {
            $nodes = DB::table(AgentNetworkManager::table('entities'))
                ->select('id', 'parent_id', 'name', 'type', 'status', 'depth')
                ->orderBy('depth')->orderBy('name')
                ->get()->map(fn ($r) => (array) $r)->keyBy('id')->toArray();

            $this->allNodes = $nodes;
            $this->roots    = collect($nodes)->whereNull('parent_id')->pluck('id')->toArray();
            $this->expanded = collect($nodes)->where('depth', '<=', 1)->pluck('id')->toArray();

            $this->stats = [
                'total'  => count($nodes),
                'active' => collect($nodes)->where('status', 'active')->count(),
                'depth'  => collect($nodes)->max('depth') ?? 0,
                'types'  => collect($nodes)->groupBy('type')->map->count()->toArray(),
            ];
        } catch (\Throwable) {
            $this->allNodes = [];
            $this->stats    = [];
        }
    }

    private function loadEntityTypes(): void
    {
        try {
            $fromConfig = array_keys(config('agent-network.entity.types', []));
            if (!empty($fromConfig)) {
                $this->entityTypes = $fromConfig;
                return;
            }
            $this->entityTypes = DB::table(AgentNetworkManager::table('entities'))
                ->distinct()->pluck('type')->filter()->sort()->values()->toArray();
        } catch (\Throwable) {
            $this->entityTypes = [];
        }
    }

    private function resetForm(): void
    {
        $this->form = ['id' => null, 'name' => '', 'type' => '', 'parent_id' => '', 'status' => 'active'];
    }

    public function toggle(int $id): void
    {
        $this->expanded = in_array($id, $this->expanded)
            ? array_values(array_filter($this->expanded, fn ($i) => $i !== $id))
            : [...$this->expanded, $id];
    }

    public function expandAll(): void   { $this->expanded = array_keys($this->allNodes); }
    public function collapseAll(): void { $this->expanded = $this->roots; }

    public function openCreate(): void
    {
        $this->formMode = 'create';
        $this->showForm = true;
        $this->resetForm();
    }

    public function openEdit(int $id): void
    {
        $node = $this->allNodes[$id] ?? null;
        if (!$node) return;

        $this->formMode = 'edit';
        $this->showForm = true;
        $this->form = [
            'id'        => $node['id'],
            'name'      => $node['name'],
            'type'      => $node['type'],
            'parent_id' => $node['parent_id'] ?? '',
            'status'    => $node['status'],
        ];
    }

    public function cancelForm(): void
    {
        $this->showForm = false;
        $this->resetForm();
    }

    public function saveActor(): void
    {
        try {
            $table    = AgentNetworkManager::table('entities');
            $parentId = $this->form['parent_id'] !== '' ? (int) $this->form['parent_id'] : null;
            $depth    = 0;

            if ($parentId) {
                $parent = DB::table($table)->where('id', $parentId)->value('depth');
                $depth  = ($parent ?? 0) + 1;
            }

            if ($this->formMode === 'create') {
                DB::table($table)->insert([
                    'ulid'       => (string) Str::ulid(),
                    'parent_id'  => $parentId,
                    'type'       => $this->form['type'],
                    'depth'      => $depth,
                    'name'       => $this->form['name'],
                    'status'     => $this->form['status'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                Notification::make()->title('Actor added.')->success()->send();
            } else {
                DB::table($table)->where('id', $this->form['id'])->update([
                    'parent_id'  => $parentId,
                    'type'       => $this->form['type'],
                    'depth'      => $depth,
                    'name'       => $this->form['name'],
                    'status'     => $this->form['status'],
                    'updated_at' => now(),
                ]);
                Notification::make()->title('Actor updated.')->success()->send();
            }

            $this->showForm = false;
            $this->resetForm();
            $this->loadTree();
        } catch (\Throwable $e) {
            Notification::make()->title('Error')->body($e->getMessage())->danger()->send();
        }
    }

    public function toggleStatus(int $id): void
    {
        try {
            $node = $this->allNodes[$id] ?? null;
            if (!$node) return;

            $new = $node['status'] === 'active' ? 'inactive' : 'active';
            DB::table(AgentNetworkManager::table('entities'))
                ->where('id', $id)
                ->update(['status' => $new, 'updated_at' => now()]);

            $this->loadTree();
        } catch (\Throwable $e) {
            Notification::make()->title('Error')->body($e->getMessage())->danger()->send();
        }
    }

    public function render()
    {
        $filtered = $this->search
            ? collect($this->allNodes)
                ->filter(fn ($n) => str_contains(strtolower($n['name']), strtolower($this->search)))
                ->values()->toArray()
            : [];

        return view('agent-network::livewire.network-tree', compact('filtered'))
            ->layout('agent-network::layout', ['title' => 'Network']);
    }
}
