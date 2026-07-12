<div>

{{-- Step indicator --}}
<div style="display:flex;align-items:center;margin-bottom:28px">
    @foreach([1=>'Basic Config', 2=>'Review & Generate'] as $n => $label)
    <div style="display:flex;align-items:center;gap:8px">
        <div style="width:26px;height:26px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:12px;font-weight:700;flex-shrink:0;
            {{ $currentStep > $n ? 'background:rgb(16 185 129);color:#fff' : ($currentStep === $n ? 'background:var(--primary-600,#6366f1);color:#fff' : 'background:rgb(229 231 235);color:rgb(107 114 128)') }}">
            {{ $currentStep > $n ? '✓' : $n }}
        </div>
        <span style="font-size:12px;font-weight:{{ $currentStep===$n?'600':'400' }};color:{{ $currentStep===$n?'rgb(17 24 39)':'rgb(107 114 128)' }}">{{ $label }}</span>
    </div>
    @if($n < 2)
    <div style="flex:1;height:1px;background:rgb(229 231 235);margin:0 12px;min-width:24px"></div>
    @endif
    @endforeach
</div>

@if($this->isGenerated())
<div style="display:flex;align-items:center;gap:10px;padding:12px 16px;background:rgb(240 253 244);border:1px solid rgb(187 247 208);border-radius:8px;margin-bottom:20px">
    <span class="ani sm" style="color:rgb(16 185 129);flex-shrink:0">check_circle</span>
    <p style="font-size:13px;color:rgb(22 101 52);margin:0">
        Generator sudah pernah dijalankan. Konfigurasi ada di <code style="background:rgb(220 252 231);padding:1px 5px;border-radius:3px;font-family:'JetBrains Mono',monospace">config/agent-network.php</code>.
        Jalankan ulang hanya jika perlu generate ulang — file lama akan ditimpa.
    </p>
</div>
@endif

{{-- ═══════════════════════ STEP 1 ═══════════════════════ --}}
@if($currentStep === 1)
<div style="display:flex;flex-direction:column;gap:16px">

    <div class="wz-card">
        <div class="wz-card-hd">
            <h2>Network Identity</h2>
            <p>Nama dan identitas dasar sistem jaringan ini.</p>
        </div>
        <div class="wz-card-bd" style="display:grid;grid-template-columns:1fr 1fr;gap:16px">
            <div>
                <label class="wz-label">Network Name</label>
                <input class="wz-input" type="text" wire:model="data.network_name" placeholder="Misal: Jaringan Agen Sejahtera"/>
                <p class="wz-hint">Label tampilan di dashboard.</p>
            </div>
            <div>
                <label class="wz-label">Entity Name <span style="color:rgb(239 68 68)">*</span></label>
                <input class="wz-input" type="text" wire:model="data.entity_name" placeholder="agent"/>
                <p class="wz-hint">Jadi nama model, tabel, dan relasi. Huruf kecil, tanpa spasi.<br>
                    Contoh: <code style="font-family:'JetBrains Mono',monospace">agent</code> → model <code style="font-family:'JetBrains Mono',monospace">Agent</code>, tabel <code style="font-family:'JetBrains Mono',monospace">an_agents</code></p>
            </div>
            <div>
                <label class="wz-label">Table Prefix</label>
                <input class="wz-input" type="text" wire:model="data.table_prefix" placeholder="an_"/>
                <p class="wz-hint">Prefix semua tabel yang di-generate. Default: <code style="font-family:'JetBrains Mono',monospace">an_</code></p>
            </div>
            <div>
                <label class="wz-label">Currency</label>
                <input class="wz-input" type="text" wire:model="data.currency" placeholder="IDR"/>
                <p class="wz-hint">Kode mata uang untuk format tampilan. Contoh: IDR, USD, MYR</p>
            </div>
        </div>
    </div>

    <div style="display:flex;justify-content:flex-end">
        <button wire:click="nextStep" class="btn-primary">
            Review & Generate <span class="ani sm f">arrow_forward</span>
        </button>
    </div>

</div>
@endif

{{-- ═══════════════════════ STEP 2 ═══════════════════════ --}}
@if($currentStep === 2)
<div style="display:flex;flex-direction:column;gap:16px">

    {{-- 1. Summary --}}
    <div class="wz-card">
        <div class="wz-card-hd"><h2>1. Ringkasan Konfigurasi</h2></div>
        <div class="wz-card-bd" style="display:grid;grid-template-columns:repeat(4,1fr);gap:16px">
            <div>
                <span class="cm-lbl">Network</span>
                <div style="font-size:14px;font-weight:600;color:rgb(17 24 39)">{{ $data['network_name'] ?: '(tidak diisi)' }}</div>
            </div>
            <div>
                <span class="cm-lbl">Entity / Model</span>
                <div style="font-size:14px;font-weight:600;color:rgb(17 24 39);font-family:'JetBrains Mono',monospace">{{ ucfirst($data['entity_name']) }}</div>
            </div>
            <div>
                <span class="cm-lbl">Table Prefix</span>
                <div style="font-size:14px;font-weight:600;color:rgb(17 24 39);font-family:'JetBrains Mono',monospace">{{ $data['table_prefix'] }}</div>
            </div>
            <div>
                <span class="cm-lbl">Currency</span>
                <div style="font-size:14px;font-weight:600;color:rgb(17 24 39)">{{ $data['currency'] }}</div>
            </div>
        </div>
    </div>

    {{-- 2. Cara Kerja --}}
    <div class="wz-card">
        <div class="wz-card-hd">
            <h2>2. Cara Kerja Sistem</h2>
            <p>Alur dari transaksi masuk sampai komisi tercatat di ledger.</p>
        </div>
        <div class="wz-card-bd">
            <div style="display:flex;align-items:center;gap:0;flex-wrap:wrap;margin-bottom:20px;padding:16px;background:rgb(249 250 251);border:1px solid rgb(229 231 235);border-radius:8px">
                @foreach([
                    ['icon'=>'bolt',                   'label'=>'AgentNetwork::transact()', 'sub'=>'Entry point di host app'],
                    ['icon'=>'arrow_forward',           'label'=>'',                         'sub'=>''],
                    ['icon'=>'receipt_long',            'label'=>'Transaction',              'sub'=>'Dicatat ke DB'],
                    ['icon'=>'arrow_forward',           'label'=>'',                         'sub'=>''],
                    ['icon'=>'notifications',           'label'=>'Event fired',              'sub'=>'TransactionRegistered'],
                    ['icon'=>'arrow_forward',           'label'=>'',                         'sub'=>''],
                    ['icon'=>'calculate',               'label'=>'Hitung Komisi',            'sub'=>'Queue listener'],
                    ['icon'=>'arrow_forward',           'label'=>'',                         'sub'=>''],
                    ['icon'=>'account_balance_wallet',  'label'=>'Commission Ledger',        'sub'=>'Status: pending'],
                ] as $step)
                @if($step['label'] === '')
                <span class="ani sm" style="color:rgb(156 163 175);margin:0 4px">arrow_forward</span>
                @else
                <div style="text-align:center;padding:8px 10px">
                    <span class="ani" style="color:var(--primary-600,#6366f1);font-size:20px;display:block">{{ $step['icon'] }}</span>
                    <div style="font-size:11px;font-weight:600;color:rgb(17 24 39);margin-top:4px;white-space:nowrap">{{ $step['label'] }}</div>
                    <div style="font-size:10px;color:rgb(156 163 175);white-space:nowrap">{{ $step['sub'] }}</div>
                </div>
                @endif
                @endforeach
            </div>

            <div style="display:flex;flex-direction:column;gap:10px">
                <div style="padding:12px 14px;background:rgb(237 233 254);border-radius:6px">
                    <div style="display:flex;align-items:center;gap:8px;margin-bottom:6px">
                        <span class="ani sm" style="color:rgb(109 40 217)">person</span>
                        <span style="font-size:13px;font-weight:600;color:rgb(76 29 149)">Personal Commission</span>
                    </div>
                    <p style="font-size:12px;color:rgb(91 33 182);margin:0;line-height:1.6">
                        Pelaku transaksi langsung mendapat komisi. Rate diatur di <strong>/commissions → Personal</strong>.
                        Contoh: transaksi 1.000.000 dengan rate 5% → dapat 50.000.
                    </p>
                </div>
                <div style="padding:12px 14px;background:rgb(219 234 254);border-radius:6px">
                    <div style="display:flex;align-items:center;gap:8px;margin-bottom:6px">
                        <span class="ani sm" style="color:rgb(29 78 216)">stacked_line_chart</span>
                        <span style="font-size:13px;font-weight:600;color:rgb(30 64 175)">Level Commission</span>
                    </div>
                    <p style="font-size:12px;color:rgb(29 78 216);margin:0;line-height:1.6">
                        Setiap upline di tiap level hierarki mendapat komisi. Level 1 = parent langsung, Level 2 = grandparent, dst.
                        Rate tiap level diatur di <strong>/commissions → Level</strong>.
                    </p>
                </div>
                <div style="padding:12px 14px;background:rgb(220 252 231);border-radius:6px">
                    <div style="display:flex;align-items:center;gap:8px;margin-bottom:6px">
                        <span class="ani sm" style="color:rgb(22 101 52)">groups</span>
                        <span style="font-size:13px;font-weight:600;color:rgb(20 83 45)">Group Commission</span>
                    </div>
                    <p style="font-size:12px;color:rgb(22 101 52);margin:0;line-height:1.6">
                        Parent langsung dari pelaku transaksi mendapat komisi per transaksi. Rate diatur di <strong>/commissions → Group</strong>.
                        Contoh: transaksi 1.000.000 dengan rate 2% → parent dapat 20.000.
                    </p>
                </div>
            </div>
        </div>
    </div>

    {{-- 3. File yang Di-generate --}}
    <div class="wz-card">
        <div class="wz-card-hd">
            <h2>3. File yang Akan Di-generate</h2>
            <p>File ini ditulis ke aplikasi host dan bisa diedit bebas setelahnya.</p>
        </div>
        <div class="wz-card-bd" style="display:grid;grid-template-columns:1fr 1fr;gap:8px">
        @php
        $p = $data['table_prefix'];
        $e = strtolower($data['entity_name']);
        $E = ucfirst($e);
        $fileGroups = [
            'Migrations' => [
                "create_{$e}s_table",
                "create_{$p}commission_rules_table",
                "create_{$p}commission_ledgers_table",
                "create_{$p}transactions_table",
            ],
            'Models' => [
                "Models/AgentNetwork/{$E}.php",
                "Models/AgentNetwork/CommissionRule.php",
                "Models/AgentNetwork/CommissionLedger.php",
                "Models/AgentNetwork/Transaction.php",
            ],
            'Events & Listeners' => [
                'TransactionRegistered → OnTransactionRegistered',
                'CommissionEarned → OnCommissionEarned',
            ],
            'Jobs & Config' => [
                'ProcessPayoutBatch.php',
                'config/agent-network.php',
            ],
        ];
        @endphp
        @foreach($fileGroups as $group => $files)
        <div style="background:rgb(249 250 251);border:1px solid rgb(229 231 235);border-radius:6px;padding:12px">
            <div style="font-size:11px;font-weight:600;text-transform:uppercase;letter-spacing:.04em;color:rgb(107 114 128);margin-bottom:8px">{{ $group }}</div>
            @foreach($files as $file)
            <div style="display:flex;align-items:center;gap:6px;padding:2px 0">
                <span class="ani sm" style="color:rgb(16 185 129);font-size:13px">check</span>
                <span style="font-size:11.5px;color:rgb(55 65 81);font-family:'JetBrains Mono',monospace">{{ $file }}</span>
            </div>
            @endforeach
        </div>
        @endforeach
        </div>
    </div>

    {{-- 4. Langkah Setelah Generate --}}
    <div class="wz-card">
        <div class="wz-card-hd"><h2>4. Langkah Setelah Generate</h2></div>
        <div class="wz-card-bd">
            <div style="display:flex;flex-direction:column;gap:8px">
                @foreach([
                    ['step'=>'1','cmd'=>'php artisan migrate',        'desc'=>'Buat semua tabel di database'],
                    ['step'=>'2','cmd'=>'/commissions',               'desc'=>'Setup rates untuk Personal, Level, dan Group'],
                    ['step'=>'3','cmd'=>'/network',                   'desc'=>'Tambah member & atur hierarki upline-downline'],
                    ['step'=>'4','cmd'=>'AgentNetwork::transact(…)',  'desc'=>'Panggil dari host app saat ada transaksi'],
                    ['step'=>'5','cmd'=>'/dashboard',                 'desc'=>'Monitor komisi, transaksi, dan payout'],
                ] as $s)
                <div style="display:flex;align-items:center;gap:12px">
                    <span style="width:20px;height:20px;border-radius:50%;background:var(--primary-600,#6366f1);color:#fff;font-size:10px;font-weight:700;display:flex;align-items:center;justify-content:center;flex-shrink:0">{{ $s['step'] }}</span>
                    <code style="font-size:12px;font-family:'JetBrains Mono',monospace;background:rgb(243 244 246);color:rgb(55 65 81);padding:4px 10px;border-radius:5px;white-space:nowrap;flex-shrink:0">{{ $s['cmd'] }}</code>
                    <span style="font-size:13px;color:rgb(75 85 99)">{{ $s['desc'] }}</span>
                </div>
                @endforeach
            </div>
        </div>
    </div>

    <div style="display:flex;align-items:center;justify-content:space-between">
        <button wire:click="prevStep" class="btn-ghost">
            <span class="ani sm">arrow_back</span> Back
        </button>
        <button wire:click="generate" class="btn-primary" style="padding:10px 28px;font-size:14px">
            <span class="ani sm f">auto_fix_high</span> Generate Files
        </button>
    </div>

</div>
@endif

</div>
