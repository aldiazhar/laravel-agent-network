<div>

{{-- Stats row --}}
<div style="display:grid;grid-template-columns:repeat(5,1fr);gap:12px;margin-bottom:20px">
    @php
    $statCards = [
        ['icon'=>'people',       'label'=>'Total Entities',   'value'=> isset($this->stats['total_entities'])   ? number_format($this->stats['total_entities'])   : '—', 'sub'=> isset($this->stats['active_entities']) ? $this->stats['active_entities'].' active' : 'no data'],
        ['icon'=>'receipt_long', 'label'=>'Transactions MTD', 'value'=> isset($this->stats['transactions_mtd']) ? number_format($this->stats['transactions_mtd']) : '—', 'sub'=>'This month'],
        ['icon'=>'payments',     'label'=>'Commissions MTD',  'value'=> isset($this->stats['commissions_mtd'])  ? \AgentNetwork\AgentNetworkManager::formatAmount($this->stats['commissions_mtd']) : '—', 'sub'=>'Earned this month'],
        ['icon'=>'schedule_send','label'=>'Pending Payouts',  'value'=> isset($this->stats['pending_payouts'])  ? \AgentNetwork\AgentNetworkManager::formatAmount($this->stats['pending_payouts'])  : '—', 'sub'=>'Awaiting disbursement'],
        ['icon'=>'check_circle', 'label'=>'Paid MTD',         'value'=> isset($this->stats['paid_mtd'])         ? \AgentNetwork\AgentNetworkManager::formatAmount($this->stats['paid_mtd'])         : '—', 'sub'=>'Disbursed this month'],
    ];
    @endphp

    @foreach($statCards as $card)
    <div style="background:#fff;border:1px solid rgb(229 231 235);border-radius:8px;padding:16px">
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:10px">
            <span style="font-size:12px;font-weight:500;color:rgb(107 114 128)">{{ $card['label'] }}</span>
            <span class="ani sm" style="color:rgb(156 163 175)">{{ $card['icon'] }}</span>
        </div>
        <div style="font-size:20px;font-weight:700;color:rgb(17 24 39);font-family:'JetBrains Mono',monospace;line-height:1">{{ $card['value'] }}</div>
        <div style="font-size:11px;color:rgb(156 163 175);margin-top:5px">{{ $card['sub'] }}</div>
    </div>
    @endforeach
</div>

<div style="display:grid;grid-template-columns:1fr 300px;gap:16px">

    {{-- Recent transactions --}}
    <div style="background:#fff;border:1px solid rgb(229 231 235);border-radius:8px;overflow:hidden">
        <div style="padding:14px 16px;border-bottom:1px solid rgb(229 231 235);display:flex;align-items:center;justify-content:space-between">
            <span style="font-size:13px;font-weight:600;color:rgb(17 24 39)">Recent Transactions</span>
            <a href="{{ route('agent-network.transactions') }}" style="font-size:12px;color:var(--primary-600);text-decoration:none;display:flex;align-items:center;gap:3px">
                View all <span class="ani sm" style="font-size:14px">arrow_forward</span>
            </a>
        </div>
        @if(empty($this->recentTransactions))
        <div style="padding:40px;text-align:center;color:rgb(156 163 175)">
            <span class="ani" style="font-size:28px;display:block;margin-bottom:8px">inbox</span>
            <p style="font-size:13px;margin:0">No transactions yet.</p>
        </div>
        @else
        <table style="width:100%;border-collapse:collapse">
            <thead>
                <tr>
                    <th class="sim-th" style="text-align:left">Entity</th>
                    <th class="sim-th" style="text-align:right">Amount</th>
                    <th class="sim-th" style="text-align:right">Date</th>
                </tr>
            </thead>
            <tbody>
                @foreach($this->recentTransactions as $tx)
                <tr>
                    <td class="sim-td">
                        <div style="font-weight:500;color:rgb(17 24 39)">{{ $tx['entity_name'] }}</div>
                        <div style="font-size:11px;color:rgb(156 163 175)">{{ ucfirst($tx['entity_type']) }}</div>
                    </td>
                    <td class="sim-td" style="text-align:right;font-family:'JetBrains Mono',monospace;font-size:12px;font-weight:600;color:rgb(17 24 39)">
                        {{ \AgentNetwork\AgentNetworkManager::formatAmount($tx['amount']) }}
                    </td>
                    <td class="sim-td" style="text-align:right;font-size:11px;color:rgb(156 163 175);white-space:nowrap">
                        {{ \Carbon\Carbon::parse($tx['created_at'])->format('d M Y') }}
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @endif
    </div>

    {{-- Commission breakdown --}}
    <div style="background:#fff;border:1px solid rgb(229 231 235);border-radius:8px;overflow:hidden;align-self:start">
        <div style="padding:14px 16px;border-bottom:1px solid rgb(229 231 235)">
            <span style="font-size:13px;font-weight:600;color:rgb(17 24 39)">Commission MTD by Type</span>
        </div>
        @if(empty($this->commissionBreakdown))
        <div style="padding:32px;text-align:center;color:rgb(156 163 175)">
            <p style="font-size:13px;margin:0">No commissions this month.</p>
        </div>
        @else
        @php $totalBreakdown = collect($this->commissionBreakdown)->sum('total'); @endphp
        <div style="padding:14px 16px;display:flex;flex-direction:column;gap:10px">
            @foreach($this->commissionBreakdown as $row)
            @php $pct = $totalBreakdown > 0 ? round(($row['total'] / $totalBreakdown) * 100) : 0; @endphp
            <div>
                <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:4px">
                    <span style="font-size:12px;color:rgb(55 65 81)">{{ ucfirst($row['commission_type']) }}</span>
                    <span style="font-size:12px;font-weight:600;color:rgb(17 24 39);font-family:'JetBrains Mono',monospace">{{ \AgentNetwork\AgentNetworkManager::formatAmount($row['total']) }}</span>
                </div>
                <div style="height:4px;background:rgb(243 244 246);border-radius:99px;overflow:hidden">
                    <div style="height:100%;width:{{ $pct }}%;background:var(--primary-600,#6366f1);border-radius:99px"></div>
                </div>
            </div>
            @endforeach
        </div>
        <div style="padding:12px 16px;border-top:1px solid rgb(243 244 246);display:flex;justify-content:space-between">
            <span style="font-size:11px;color:rgb(156 163 175)">Total earned MTD</span>
            <span style="font-size:12px;font-weight:700;color:rgb(17 24 39);font-family:'JetBrains Mono',monospace">{{ \AgentNetwork\AgentNetworkManager::formatAmount($totalBreakdown) }}</span>
        </div>
        @endif
    </div>

</div>

</div>
