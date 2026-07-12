<?php

namespace AgentNetwork;

use AgentNetwork\Console\InstallCommand;
use AgentNetwork\Livewire\CommissionRules;
use AgentNetwork\Livewire\Dashboard;
use AgentNetwork\Livewire\NetworkTree;
use AgentNetwork\Livewire\PayoutQueue;
use AgentNetwork\Livewire\SetupWizard;
use AgentNetwork\Livewire\Transactions;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Livewire\Livewire;

class AgentNetworkServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton('agent-network', fn () => new AgentNetworkManager());
    }

    public function boot(): void
    {
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'agent-network');

        Livewire::component('agent-network-dashboard',        Dashboard::class);
        Livewire::component('agent-network-setup-wizard',     SetupWizard::class);
        Livewire::component('agent-network-commission-rules', CommissionRules::class);
        Livewire::component('agent-network-transactions',     Transactions::class);
        Livewire::component('agent-network-network-tree',     NetworkTree::class);
        Livewire::component('agent-network-payout-queue',     PayoutQueue::class);

        $this->registerRoutes();

        if ($this->app->runningInConsole()) {
            $this->commands([InstallCommand::class]);
        }
    }

    protected function registerRoutes(): void
    {
        Route::middleware('web')
            ->prefix('agent-network')
            ->name('agent-network.')
            ->group(function () {
                Route::get('/',             fn () => redirect()->route('agent-network.dashboard'));
                Route::get('/dashboard',    Dashboard::class)->name('dashboard');
                Route::get('/setup',        SetupWizard::class)->name('setup');
                Route::get('/commissions',  CommissionRules::class)->name('commissions');
                Route::get('/transactions', Transactions::class)->name('transactions');
                Route::get('/network',      NetworkTree::class)->name('network');
                Route::get('/payouts',      PayoutQueue::class)->name('payouts');
            });
    }
}
