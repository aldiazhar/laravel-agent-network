<?php

namespace AgentNetwork\Console;

use Illuminate\Console\Command;

class InstallCommand extends Command
{
    protected $signature = 'agent-network:install';

    protected $description = 'Install the Agent Network package';

    public function handle(): void
    {
        $this->info('Agent Network installed successfully.');
        $this->newLine();
        $this->line('Next step — register the plugin in your Filament panel provider:');
        $this->newLine();
        $this->line('  ->plugins([');
        $this->line('      \AgentNetwork\Filament\AgentNetworkPlugin::make(),');
        $this->line('  ])');
        $this->newLine();
        $this->line('Then open your browser and navigate to your Filament panel to run the setup wizard.');
    }
}
