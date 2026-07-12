<?php

namespace AgentNetwork\Livewire;

use AgentNetwork\Console\GeneratorEngine;
use Filament\Notifications\Notification;
use Livewire\Component;

class SetupWizard extends Component
{
    public array $data        = [];
    public int   $currentStep = 1;

    public function mount(): void
    {
        $this->data = [
            'network_name' => '',
            'entity_name'  => 'agent',
            'table_prefix' => 'an_',
            'currency'     => 'IDR',
        ];
    }

    public function isGenerated(): bool
    {
        return file_exists(config_path('agent-network.php'));
    }

    public function nextStep(): void { if ($this->currentStep < 2) $this->currentStep++; }
    public function prevStep(): void { if ($this->currentStep > 1) $this->currentStep--; }

    public function generate(): void
    {
        try {
            $written = (new GeneratorEngine($this->data))->run();
            Notification::make()
                ->title('Generated successfully!')
                ->body(count($written) . ' files written. Run php artisan migrate to complete setup.')
                ->success()
                ->send();
        } catch (\Throwable $e) {
            Notification::make()
                ->title('Generator failed')
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }

    public function render()
    {
        return view('agent-network::livewire.setup-wizard')
            ->layout('agent-network::layout', ['title' => 'Generator']);
    }
}
