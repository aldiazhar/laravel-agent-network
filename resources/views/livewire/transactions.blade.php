<div>

{{-- Toolbar --}}
<div style="display:flex;align-items:center;gap:10px;margin-bottom:16px;flex-wrap:wrap">
    <div style="position:relative;flex:1;min-width:200px">
        <span class="ani sm" style="position:absolute;left:10px;top:50%;transform:translateY(-50%);color:rgb(156 163 175);pointer-events:none">search</span>
        <input class="wz-input" type="text" wire:model.live.debounce.300ms="search"
               placeholder="Search entity name..." style="padding-left:34px"/>
    </div>
    <input class="wz-input" type="date" wire:model.live="dateFrom" style="width:auto"/>
    <span style="font-size:12px;color:rgb(156 163 175)">–</span>
    <input class="wz-input" type="date" wire:model.live="dateTo" style="width:auto"/>
    @if($this->search || $this->dateFrom || $this->dateTo)
    <button wire:click="$set('search',''); $set('dateFrom',''); $set('dateTo','')" class="btn-ghost" style="padding:8px 12px">
        <span class="ani sm">close</span> Clear
    </button>
    @endif
</div>

@if($total > 0)
<div style="display:flex;align-items:center;justify-content:space-between;padding:9px 14px;background:#fff;border:1px solid rgb(229 231 235);border-radius:6px;margin-bottom:12px">
    <span style="font-size:12px;color:rgb(107 114 128)">{{ $transactions?->total() ?? 0 }} transactions</span>
    <span style="font-size:12px;font-weight:600;color:rgb(17 24 39);font-family:'JetBrains Mono',monospace">
        Total: {{ \AgentNetwork\AgentNetworkManager::formatAmount($total) }}
    </span>
</div>
@endif

<div style="background:#fff;border:1px solid rgb(229 231 235);border-radius:8px;overflow:hidden">
    @if(!$transactions || $transactions->isEmpty())
    <div style="padding:48px;text-align:center;color:rgb(156 163 175)">
        <span class="ani" style="font-size:28px;display:block;margin-bottom:8px">inbox</span>
        <p style="font-size:13px;margin:0">No transactions found.</p>
    </div>
    @else
    <table style="width:100%;border-collapse:collapse">
        <thead>
            <tr>
                <th class="sim-th" style="text-align:left">#</th>
                <th class="sim-th" style="text-align:left">Entity</th>
                <th class="sim-th" style="text-align:left">Type</th>
                <th class="sim-th" style="text-align:right">Amount</th>
                <th class="sim-th" style="text-align:right">Date</th>
            </tr>
        </thead>
        <tbody>
            @foreach($transactions as $tx)
            <tr>
                <td class="sim-td" style="color:rgb(156 163 175);font-size:11px;font-family:'JetBrains Mono',monospace">{{ $tx->id }}</td>
                <td class="sim-td">
                    <div style="font-weight:500;color:rgb(17 24 39)">{{ $tx->entity_name }}</div>
                </td>
                <td class="sim-td">
                    <span style="font-size:11px;font-weight:500;background:rgb(243 244 246);color:rgb(75 85 99);padding:2px 8px;border-radius:4px">{{ ucfirst($tx->entity_type) }}</span>
                </td>
                <td class="sim-td" style="text-align:right;font-family:'JetBrains Mono',monospace;font-size:12px;font-weight:600;color:rgb(17 24 39)">
                    {{ \AgentNetwork\AgentNetworkManager::formatAmount($tx->amount) }}
                </td>
                <td class="sim-td" style="text-align:right;font-size:11px;color:rgb(156 163 175);white-space:nowrap">
                    {{ \Carbon\Carbon::parse($tx->created_at)->format('d M Y, H:i') }}
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @if($transactions->hasPages())
    <div style="padding:10px 16px;border-top:1px solid rgb(229 231 235);background:rgb(249 250 251)">
        {{ $transactions->links() }}
    </div>
    @endif
    @endif
</div>

</div>
