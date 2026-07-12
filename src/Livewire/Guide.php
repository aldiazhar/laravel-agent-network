<?php

namespace AgentNetwork\Livewire;

use Livewire\Component;

class Guide extends Component
{
    public function render()
    {
        return view('agent-network::livewire.guide')
            ->layout('agent-network::layout', ['title' => 'Usage Guide']);
    }
}
