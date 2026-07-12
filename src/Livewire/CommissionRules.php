<?php

namespace AgentNetwork\Livewire;

use AgentNetwork\AgentNetworkManager;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class CommissionRules extends Component
{
    public string $activeTab   = 'personal';
    public array  $rules       = [];
    public array  $entityTypes = [];
    public ?int   $editingId   = null;
    public array  $editForm    = [];
    public bool   $showCreate  = false;
    public array  $createForm  = [];

    public function mount(): void
    {
        $this->loadEntityTypes();
        $this->loadRules();
        $this->resetCreate();
    }

    private function loadEntityTypes(): void
    {
        try {
            $this->entityTypes = DB::table(AgentNetworkManager::table('entities'))
                ->distinct()->pluck('type')->filter()->sort()->values()->toArray();
        } catch (\Throwable) {
            $this->entityTypes = [];
        }
    }

    private function loadRules(): void
    {
        try {
            $this->rules = DB::table(AgentNetworkManager::table('rules'))
                ->orderBy('id')
                ->get()
                ->map(fn ($r) => [
                    'id'              => $r->id,
                    'commission_type' => $r->commission_type,
                    'entity_type'     => $r->entity_type,
                    'active'          => (bool) $r->active,
                    'conditions'      => json_decode($r->conditions ?? '{}', true) ?: [],
                ])
                ->groupBy('commission_type')
                ->map->values()
                ->toArray();
        } catch (\Throwable) {
            $this->rules = [];
        }
    }

    private function defaultConditions(string $type): array
    {
        return match ($type) {
            'personal' => ['rate' => ''],
            'level'    => ['rates' => [
                ['level' => 1, 'rate' => ''],
                ['level' => 2, 'rate' => ''],
            ]],
            'group'    => ['rate' => ''],
            default    => [],
        };
    }

    private function resetCreate(): void
    {
        $this->createForm = [
            'entity_type' => '',
            'conditions'  => $this->defaultConditions($this->activeTab),
        ];
    }

    public function updatedActiveTab(): void
    {
        $this->showCreate = false;
        $this->cancelEdit();
        $this->resetCreate();
    }

    public function toggleActive(int $id, bool $current): void
    {
        try {
            DB::table(AgentNetworkManager::table('rules'))->where('id', $id)->update(['active' => ! $current]);
            $this->loadRules();
        } catch (\Throwable $e) {
            Notification::make()->title('Error')->body($e->getMessage())->danger()->send();
        }
    }

    public function startEdit(int $id): void
    {
        $rule = collect($this->rules)->flatten(1)->firstWhere('id', $id);
        if (! $rule) return;
        $this->editingId  = $id;
        $this->editForm   = $rule;
        $this->showCreate = false;
    }

    public function cancelEdit(): void { $this->editingId = null; $this->editForm = []; }

    public function saveEdit(): void
    {
        try {
            DB::table(AgentNetworkManager::table('rules'))->where('id', $this->editingId)->update([
                'entity_type' => $this->editForm['entity_type'] ?: null,
                'conditions'  => json_encode($this->editForm['conditions']),
            ]);
            $this->cancelEdit();
            $this->loadRules();
            Notification::make()->title('Rule updated.')->success()->send();
        } catch (\Throwable $e) {
            Notification::make()->title('Error')->body($e->getMessage())->danger()->send();
        }
    }

    public function deleteRule(int $id): void
    {
        try {
            DB::table(AgentNetworkManager::table('rules'))->where('id', $id)->delete();
            $this->loadRules();
            Notification::make()->title('Rule deleted.')->success()->send();
        } catch (\Throwable $e) {
            Notification::make()->title('Error')->body($e->getMessage())->danger()->send();
        }
    }

    public function saveCreate(): void
    {
        try {
            DB::table(AgentNetworkManager::table('rules'))->insert([
                'commission_type' => $this->activeTab,
                'entity_type'     => $this->createForm['entity_type'] ?: null,
                'active'          => true,
                'conditions'      => json_encode($this->createForm['conditions']),
            ]);
            $this->showCreate = false;
            $this->resetCreate();
            $this->loadRules();
            Notification::make()->title('Rule created.')->success()->send();
        } catch (\Throwable $e) {
            Notification::make()->title('Error')->body($e->getMessage())->danger()->send();
        }
    }

    public function addLevelRate(string $form): void
    {
        $next = count($this->{$form . 'Form'}['conditions']['rates'] ?? []) + 1;
        $this->{$form . 'Form'}['conditions']['rates'][] = ['level' => $next, 'rate' => ''];
    }

    public function removeLevelRate(string $form, int $i): void
    {
        array_splice($this->{$form . 'Form'}['conditions']['rates'], $i, 1);
        $this->{$form . 'Form'}['conditions']['rates'] = array_values($this->{$form . 'Form'}['conditions']['rates']);
    }

    public function render()
    {
        return view('agent-network::livewire.commission-rules', [
            'tabRules' => $this->rules[$this->activeTab] ?? [],
        ])->layout('agent-network::layout', ['title' => 'Commission Rules']);
    }
}
