<div>

@if(!\AgentNetwork\AgentNetworkManager::isSetup())
<div style="display:flex;align-items:center;gap:10px;padding:12px 16px;background:rgb(249 250 251);border:1px solid rgb(229 231 235);border-radius:8px;margin-bottom:20px">
    <span class="ani sm" style="color:rgb(107 114 128);flex-shrink:0">construction</span>
    <p style="font-size:13px;color:rgb(75 85 99);margin:0">
        Generator belum dijalankan. Buka <a href="{{ route('agent-network.setup') }}" style="font-weight:600;color:var(--primary-700,#4338ca)">Generator</a> untuk membuat struktur sistem.
    </p>
</div>
@elseif(!$this->ready)
<div style="display:flex;align-items:center;gap:10px;padding:12px 16px;background:rgb(249 250 251);border:1px solid rgb(229 231 235);border-radius:8px;margin-bottom:20px">
    <span class="ani sm" style="color:rgb(107 114 128);flex-shrink:0">storage</span>
    <p style="font-size:13px;color:rgb(75 85 99);margin:0">
        Tabel belum ada. Jalankan <code style="background:rgb(243 244 246);padding:1px 6px;border-radius:3px;font-family:'JetBrains Mono',monospace">php artisan migrate</code>.
    </p>
</div>
@endif

{{-- Summary cards --}}
<div style="display:grid;grid-template-columns:repeat(4,1fr);gap:12px;margin-bottom:20px">
    <div style="background:#fff;border:1px solid rgb(229 231 235);border-radius:8px;padding:14px 16px">
        <div style="font-size:11px;font-weight:500;color:rgb(107 114 128);margin-bottom:5px">Pending Count</div>
        <div style="font-size:22px;font-weight:700;color:rgb(17 24 39)">{{ number_format($this->summary['pending_count'] ?? 0) }}</div>
    </div>
    <div style="background:#fff;border:1px solid rgb(229 231 235);border-radius:8px;padding:14px 16px">
        <div style="font-size:11px;font-weight:500;color:rgb(107 114 128);margin-bottom:5px">Pending Amount</div>
        <div style="font-size:16px;font-weight:700;color:rgb(17 24 39);font-family:'JetBrains Mono',monospace">
            {{ \AgentNetwork\AgentNetworkManager::formatAmount($this->summary['pending_amount'] ?? 0) }}
        </div>
    </div>
    <div style="background:#fff;border:1px solid rgb(229 231 235);border-radius:8px;padding:14px 16px">
        <div style="font-size:11px;font-weight:500;color:rgb(107 114 128);margin-bottom:5px">Paid Count</div>
        <div style="font-size:22px;font-weight:700;color:rgb(17 24 39)">{{ number_format($this->summary['paid_count'] ?? 0) }}</div>
    </div>
    <div style="background:#fff;border:1px solid rgb(229 231 235);border-radius:8px;padding:14px 16px">
        <div style="font-size:11px;font-weight:500;color:rgb(107 114 128);margin-bottom:5px">Paid Amount</div>
        <div style="font-size:16px;font-weight:700;color:rgb(17 24 39);font-family:'JetBrains Mono',monospace">
            {{ \AgentNetwork\AgentNetworkManager::formatAmount($this->summary['paid_amount'] ?? 0) }}
        </div>
    </div>
</div>

{{-- Toolbar --}}
<div style="display:flex;align-items:center;gap:10px;margin-bottom:16px;flex-wrap:wrap">
    <div class="seg" style="flex:none">
        @foreach([''=>'All','pending'=>'Pending','paid'=>'Paid'] as $val => $label)
        <button class="seg-btn {{ $this->statusFilter === $val ? 'on' : '' }}"
                wire:click="$set('statusFilter','{{ $val }}')">{{ $label }}</button>
        @endforeach
    </div>

    <select class="wz-select" wire:model.live="typeFilter" style="width:auto;min-width:160px">
        <option value="">All Types</option>
        @foreach(['direct','level','group','override'] as $t)
        <option value="{{ $t }}">{{ ucfirst($t) }}</option>
        @endforeach
    </select>

    @if(!empty($this->selected))
    <button wire:click="markSelectedPaid" class="btn-primary" style="margin-left:auto;padding:8px 16px;font-size:12px">
        <span class="ani f sm">check_circle</span> Mark {{ count($this->selected) }} as Paid
    </button>
    @endif
</div>

{{-- Table --}}
<div style="background:#fff;border:1px solid rgb(229 231 235);border-radius:8px;overflow:hidden">
    @if(!$ledgers || $ledgers->isEmpty())
    <div style="padding:48px;text-align:center;color:rgb(156 163 175)">
        <span class="ani" style="font-size:28px;display:block;margin-bottom:8px">schedule_send</span>
        <p style="font-size:13px;margin:0">
            @if($this->statusFilter === 'pending') No pending payouts.
            @elseif($this->statusFilter === 'paid') No paid entries.
            @else No payout entries found.
            @endif
        </p>
    </div>
    @else
    <table style="width:100%;border-collapse:collapse">
        <thead>
            <tr>
                <th class="sim-th" style="width:32px">
                    <input type="checkbox" wire:model.live="selectAll"
                           onchange="$wire.selected = this.checked ? {{ collect($ledgers->items())->pluck('id')->toJson() }} : []"
                           style="accent-color:var(--primary-600)"/>
                </th>
                <th class="sim-th" style="text-align:left">Entity</th>
                <th class="sim-th" style="text-align:left">Type</th>
                <th class="sim-th" style="text-align:right">Amount</th>
                <th class="sim-th" style="text-align:center">Status</th>
                <th class="sim-th" style="text-align:right">Date</th>
                <th class="sim-th" style="text-align:center">Action</th>
            </tr>
        </thead>
        <tbody>
            @foreach($ledgers as $ledger)
            @php
            $isPending = $ledger->status === 'pending';
            $typeColors = [
                'direct'   => ['bg'=>'rgb(237 233 254)','text'=>'rgb(91 33 182)'],
                'level'    => ['bg'=>'rgb(219 234 254)','text'=>'rgb(29 78 216)'],
                'group'    => ['bg'=>'rgb(220 252 231)','text'=>'rgb(22 101 52)'],
                'override' => ['bg'=>'rgb(254 249 195)','text'=>'rgb(133 77 14)'],
            ];
            $tc = $typeColors[$ledger->commission_type] ?? ['bg'=>'rgb(243 244 246)','text'=>'rgb(75 85 99)'];
            @endphp
            <tr>
                <td class="sim-td" style="text-align:center">
                    @if($isPending)
                    <input type="checkbox" wire:model.live="selected" value="{{ $ledger->id }}"
                           style="accent-color:var(--primary-600)"/>
                    @endif
                </td>
                <td class="sim-td">
                    <div style="font-weight:500;color:rgb(17 24 39)">{{ $ledger->entity_name }}</div>
                    <div style="font-size:11px;color:rgb(156 163 175)">{{ ucfirst($ledger->entity_type) }}</div>
                </td>
                <td class="sim-td">
                    <span style="font-size:11px;font-weight:600;background:{{ $tc['bg'] }};color:{{ $tc['text'] }};padding:2px 8px;border-radius:4px">
                        {{ ucfirst($ledger->commission_type) }}
                        @if($ledger->level) · L{{ $ledger->level }} @endif
                    </span>
                </td>
                <td class="sim-td" style="text-align:right;font-family:'JetBrains Mono',monospace;font-size:12px;font-weight:600;color:rgb(17 24 39)">
                    {{ \AgentNetwork\AgentNetworkManager::formatAmount($ledger->amount) }}
                </td>
                <td class="sim-td" style="text-align:center">
                    @if($isPending)
                    <span style="font-size:11px;font-weight:500;background:rgb(254 249 195);color:rgb(133 77 14);padding:2px 8px;border-radius:4px;display:inline-flex;align-items:center;gap:4px">
                        <span class="ani sm" style="font-size:12px">schedule</span> Pending
                    </span>
                    @else
                    <span style="font-size:11px;font-weight:500;background:rgb(220 252 231);color:rgb(22 101 52);padding:2px 8px;border-radius:4px;display:inline-flex;align-items:center;gap:4px">
                        <span class="ani sm" style="font-size:12px">check_circle</span> Paid
                    </span>
                    @endif
                </td>
                <td class="sim-td" style="text-align:right;font-size:11px;color:rgb(156 163 175);white-space:nowrap">
                    @if($ledger->paid_at)
                        Paid {{ \Carbon\Carbon::parse($ledger->paid_at)->format('d M Y') }}
                    @else
                        {{ \Carbon\Carbon::parse($ledger->created_at)->format('d M Y') }}
                    @endif
                </td>
                <td class="sim-td" style="text-align:center">
                    @if($isPending)
                    <button wire:click="markPaid({{ $ledger->id }})" class="btn-ghost"
                            style="font-size:11px;padding:4px 10px;color:rgb(16 185 129);border-color:rgb(187 247 208)">
                        <span class="ani sm" style="font-size:13px">check</span> Pay
                    </button>
                    @else
                    <span style="font-size:11px;color:rgb(156 163 175)">—</span>
                    @endif
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>

    @if($ledgers->hasPages())
    <div style="padding:10px 16px;border-top:1px solid rgb(229 231 235);background:rgb(249 250 251)">
        {{ $ledgers->links() }}
    </div>
    @endif
    @endif
</div>

</div>
