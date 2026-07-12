<div style="max-width:860px">

    @php
    $section = fn(string $title, string $icon) =>
        "<div style='display:flex;align-items:center;gap:8px;margin:32px 0 12px'>
            <span class='ani' style='color:rgb(99 102 241)'>$icon</span>
            <h2 style='font-size:15px;font-weight:700;color:rgb(17 24 39);margin:0'>$title</h2>
        </div>";

    $code = fn(string $lang, string $body) =>
        "<pre style='background:rgb(17 24 39);color:rgb(229 231 235);border-radius:8px;padding:16px;font-size:12px;font-family:\"JetBrains Mono\",monospace;overflow-x:auto;line-height:1.6;margin:0'><code>$body</code></pre>";

    $badge = fn(string $label, string $color = 'indigo') =>
        "<span style='display:inline-block;padding:2px 10px;border-radius:99px;font-size:11px;font-weight:600;background:rgb(238 242 255);color:rgb(99 102 241)'>$label</span>";
    @endphp

    {{-- Header --}}
    <div style="background:#fff;border:1px solid rgb(229 231 235);border-radius:8px;padding:24px;margin-bottom:8px">
        <div style="display:flex;align-items:center;gap:12px;margin-bottom:8px">
            <span class="ani" style="font-size:28px;color:rgb(99 102 241)">menu_book</span>
            <div>
                <div style="font-size:18px;font-weight:700;color:rgb(17 24 39)">Usage Guide</div>
                <div style="font-size:12px;color:rgb(107 114 128);margin-top:2px">Panduan lengkap penggunaan Agent Network Engine</div>
            </div>
        </div>
    </div>

    {{-- Step 1: Installation --}}
    <div style="background:#fff;border:1px solid rgb(229 231 235);border-radius:8px;padding:24px;margin-bottom:8px">
        {!! $section('1. Instalasi', 'download') !!}
        <p style="font-size:13px;color:rgb(75 85 99);margin:0 0 12px">Install package via Composer:</p>
        {!! $code('bash', 'composer require aldiazhar/laravel-agent-network') !!}
    </div>

    {{-- Step 2: Generate Files --}}
    <div style="background:#fff;border:1px solid rgb(229 231 235);border-radius:8px;padding:24px;margin-bottom:8px">
        {!! $section('2. Generate Files via Setup Wizard', 'construction') !!}
        <p style="font-size:13px;color:rgb(75 85 99);margin:0 0 16px">
            Buka <a href="{{ route('agent-network.setup') }}" style="color:rgb(99 102 241);font-weight:500">/agent-network/setup</a>,
            isi form, klik <strong>Generate Files</strong>.
        </p>

        <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:16px">
            @foreach([
                ['field' => 'Network Name', 'desc' => 'Nama sistem (misal: Reseller Network)', 'ex' => 'Reseller Network'],
                ['field' => 'Entity Name', 'desc' => 'Nama aktor singular (misal: reseller, agent)', 'ex' => 'reseller'],
                ['field' => 'Table Prefix', 'desc' => 'Prefix tabel database', 'ex' => 'rs_'],
                ['field' => 'Currency', 'desc' => 'Simbol mata uang untuk display', 'ex' => 'IDR'],
            ] as $f)
            <div style="border:1px solid rgb(229 231 235);border-radius:6px;padding:12px">
                <div style="font-size:12px;font-weight:600;color:rgb(17 24 39);margin-bottom:2px">{{ $f['field'] }}</div>
                <div style="font-size:11px;color:rgb(107 114 128);margin-bottom:4px">{{ $f['desc'] }}</div>
                <div style="font-size:11px;font-family:'JetBrains Mono',monospace;color:rgb(99 102 241)">Contoh: {{ $f['ex'] }}</div>
            </div>
            @endforeach
        </div>

        <p style="font-size:13px;color:rgb(75 85 99);margin:0 0 12px">File yang di-generate ke app kamu:</p>
        {!! $code('', 'database/migrations/
  create_{entity}s_table.php
  create_{prefix}commission_rules_table.php
  create_{prefix}commission_ledgers_table.php
  create_{prefix}transactions_table.php

app/Models/AgentNetwork/
  {Entity}.php          ← model aktor (relasi parent/children/ancestors)
  CommissionRule.php
  CommissionLedger.php
  Transaction.php

app/Events/AgentNetwork/
  TransactionRegistered.php
  CommissionEarned.php

app/Listeners/AgentNetwork/
  OnTransactionRegistered.php   ← logika komisi (jangan edit)
  OnCommissionEarned.php        ← hook notifikasi (isi sendiri)

app/Jobs/
  ProcessPayoutBatch.php        ← hook payout (isi sendiri)

config/agent-network.php') !!}

        <p style="font-size:13px;color:rgb(75 85 99);margin:12px 0 12px">Setelah generate, jalankan migrasi:</p>
        {!! $code('bash', 'php artisan migrate') !!}
    </div>

    {{-- Step 3: Commission Rules --}}
    <div style="background:#fff;border:1px solid rgb(229 231 235);border-radius:8px;padding:24px;margin-bottom:8px">
        {!! $section('3. Buat Commission Rules', 'percent') !!}
        <p style="font-size:13px;color:rgb(75 85 99);margin:0 0 16px">
            Buka <a href="{{ route('agent-network.commissions') }}" style="color:rgb(99 102 241);font-weight:500">/agent-network/commissions</a>
            untuk membuat aturan komisi. Ada 3 tipe:
        </p>

        <div style="display:grid;gap:12px">

            {{-- Personal --}}
            <div style="border:1px solid rgb(199 210 254);border-radius:8px;padding:16px;background:rgb(238 242 255)">
                <div style="display:flex;align-items:center;gap:8px;margin-bottom:8px">
                    <span class="ani sm" style="color:rgb(99 102 241)">person</span>
                    <span style="font-size:13px;font-weight:700;color:rgb(67 56 202)">Personal</span>
                    <span style="font-size:11px;color:rgb(107 114 128)">— aktor dapat komisi dari transaksinya sendiri</span>
                </div>
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px">
                    <div>
                        <div style="font-size:11px;font-weight:600;color:rgb(107 114 128);margin-bottom:4px">Conditions</div>
                        {!! $code('json', '{ "rate": 5 }') !!}
                    </div>
                    <div>
                        <div style="font-size:11px;font-weight:600;color:rgb(107 114 128);margin-bottom:4px">Hasil</div>
                        <div style="background:rgb(17 24 39);border-radius:8px;padding:16px;font-size:12px;color:rgb(229 231 235);font-family:'JetBrains Mono',monospace;line-height:1.6">
                            Transaksi 1.000.000 × 5%<br>→ Aktor dapat <strong style="color:rgb(134 239 172)">50.000</strong>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Level --}}
            <div style="border:1px solid rgb(187 247 208);border-radius:8px;padding:16px;background:rgb(240 253 244)">
                <div style="display:flex;align-items:center;gap:8px;margin-bottom:8px">
                    <span class="ani sm" style="color:rgb(22 163 74)">account_tree</span>
                    <span style="font-size:13px;font-weight:700;color:rgb(21 128 61)">Level</span>
                    <span style="font-size:11px;color:rgb(107 114 128)">— upline dapat komisi berdasarkan kedalaman</span>
                </div>
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px">
                    <div>
                        <div style="font-size:11px;font-weight:600;color:rgb(107 114 128);margin-bottom:4px">Conditions</div>
                        {!! $code('json', '{ "rates": [
  { "level": 1, "rate": 3 },
  { "level": 2, "rate": 1 }
] }') !!}
                    </div>
                    <div>
                        <div style="font-size:11px;font-weight:600;color:rgb(107 114 128);margin-bottom:4px">Hasil</div>
                        <div style="background:rgb(17 24 39);border-radius:8px;padding:16px;font-size:12px;color:rgb(229 231 235);font-family:'JetBrains Mono',monospace;line-height:1.6">
                            Transaksi 1.000.000:<br>
                            Parent (L1) × 3% → <strong style="color:rgb(134 239 172)">30.000</strong><br>
                            Grandparent (L2) × 1% → <strong style="color:rgb(134 239 172)">10.000</strong>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Group --}}
            <div style="border:1px solid rgb(253 230 138);border-radius:8px;padding:16px;background:rgb(255 251 235)">
                <div style="display:flex;align-items:center;gap:8px;margin-bottom:8px">
                    <span class="ani sm" style="color:rgb(217 119 6)">groups</span>
                    <span style="font-size:13px;font-weight:700;color:rgb(180 83 9)">Group</span>
                    <span style="font-size:11px;color:rgb(107 114 128)">— parent langsung dapat komisi per transaksi downline</span>
                </div>
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px">
                    <div>
                        <div style="font-size:11px;font-weight:600;color:rgb(107 114 128);margin-bottom:4px">Conditions</div>
                        {!! $code('json', '{ "rate": 2 }') !!}
                    </div>
                    <div>
                        <div style="font-size:11px;font-weight:600;color:rgb(107 114 128);margin-bottom:4px">Hasil</div>
                        <div style="background:rgb(17 24 39);border-radius:8px;padding:16px;font-size:12px;color:rgb(229 231 235);font-family:'JetBrains Mono',monospace;line-height:1.6">
                            Transaksi 1.000.000 × 2%<br>→ Parent dapat <strong style="color:rgb(134 239 172)">20.000</strong>
                        </div>
                    </div>
                </div>
            </div>

        </div>

        <div style="margin-top:16px;padding:12px;background:rgb(249 250 251);border:1px solid rgb(229 231 235);border-radius:6px;font-size:12px;color:rgb(75 85 99);line-height:1.6">
            <strong>Prioritas rule:</strong> rule dengan <code>entity_type</code> spesifik (misal: <code>'reseller'</code>) selalu menang atas rule global (<code>entity_type = null</code>).
            Satu rule per tipe komisi per transaksi.
        </div>
    </div>

    {{-- Step 4: Trigger Transaksi --}}
    <div style="background:#fff;border:1px solid rgb(229 231 235);border-radius:8px;padding:24px;margin-bottom:8px">
        {!! $section('4. Trigger Transaksi dari Aplikasi', 'bolt') !!}
        <p style="font-size:13px;color:rgb(75 85 99);margin:0 0 12px">
            Panggil <code style="background:rgb(243 244 246);padding:1px 6px;border-radius:4px;font-size:12px">AgentNetwork::transact()</code>
            setiap kali ada transaksi di app kamu:
        </p>
        {!! $code('php', 'use AgentNetwork\Facades\AgentNetwork;

// Di controller, action, atau observer:
AgentNetwork::transact(
    actorId: $agent->id,
    amount:  $order->total,
    ref:     $order->id,
);') !!}

        <div style="margin-top:12px;display:grid;grid-template-columns:repeat(3,1fr);gap:8px">
            @foreach([
                ['param' => 'actorId', 'type' => 'int', 'desc' => 'ID aktor yang bertransaksi'],
                ['param' => 'amount',  'type' => 'int|float', 'desc' => 'Nominal transaksi'],
                ['param' => 'ref',     'type' => 'int|string', 'desc' => 'ID referensi (misal: order ID)'],
            ] as $p)
            <div style="border:1px solid rgb(229 231 235);border-radius:6px;padding:10px">
                <code style="font-size:12px;color:rgb(99 102 241);font-family:'JetBrains Mono',monospace">{{ $p['param'] }}</code>
                <span style="font-size:10px;color:rgb(156 163 175);margin-left:4px">{{ $p['type'] }}</span>
                <div style="font-size:11px;color:rgb(107 114 128);margin-top:4px">{{ $p['desc'] }}</div>
            </div>
            @endforeach
        </div>

        <p style="font-size:13px;color:rgb(75 85 99);margin:16px 0 12px">
            Method ini men-dispatch event <code style="background:rgb(243 244 246);padding:1px 6px;border-radius:4px;font-size:12px">TransactionRegistered</code>
            yang diproses secara async oleh queue:
        </p>
        {!! $code('bash', 'php artisan queue:listen') !!}
    </div>

    {{-- Step 5: Extend Hooks --}}
    <div style="background:#fff;border:1px solid rgb(229 231 235);border-radius:8px;padding:24px;margin-bottom:8px">
        {!! $section('5. Extend: Hook Notifikasi & Payout', 'extension') !!}
        <p style="font-size:13px;color:rgb(75 85 99);margin:0 0 16px">
            Dua file ini sengaja dikosongkan — isi sesuai kebutuhan app kamu:
        </p>

        <div style="display:grid;gap:12px">
            <div>
                <div style="font-size:12px;font-weight:600;color:rgb(75 85 99);margin-bottom:6px;font-family:'JetBrains Mono',monospace">
                    app/Listeners/AgentNetwork/OnCommissionEarned.php
                </div>
                {!! $code('php', '// Dipanggil setiap komisi berhasil dihitung
public function handle(CommissionEarned $event): void
{
    // Kirim notifikasi ke aktor
    $event->ledger->actor->notify(new CommissionNotification($event->ledger));
}') !!}
            </div>
            <div>
                <div style="font-size:12px;font-weight:600;color:rgb(75 85 99);margin-bottom:6px;font-family:'JetBrains Mono',monospace">
                    app/Jobs/ProcessPayoutBatch.php
                </div>
                {!! $code('php', '// Dipanggil saat batch payout dijalankan dari /agent-network/payouts
public function handle(): void
{
    // Transfer ke rekening / dompet digital
    foreach ($this->ledgers as $ledger) {
        BankTransfer::send($ledger->actor->account_number, $ledger->amount);
        $ledger->update([\'status\' => \'paid\', \'paid_at\' => now()]);
    }
}') !!}
            </div>
        </div>
    </div>

    {{-- Routes dashboard --}}
    <div style="background:#fff;border:1px solid rgb(229 231 235);border-radius:8px;padding:24px;margin-bottom:8px">
        {!! $section('Referensi Route Dashboard', 'map') !!}
        <table style="width:100%;border-collapse:collapse;font-size:12px">
            <thead>
                <tr style="background:rgb(249 250 251)">
                    <th style="padding:8px 12px;text-align:left;font-weight:600;color:rgb(75 85 99);border-bottom:1px solid rgb(229 231 235)">Route</th>
                    <th style="padding:8px 12px;text-align:left;font-weight:600;color:rgb(75 85 99);border-bottom:1px solid rgb(229 231 235)">Fungsi</th>
                </tr>
            </thead>
            <tbody>
                @foreach([
                    ['/agent-network/setup',       'agent-network.setup',       'Generator wizard — generate semua file'],
                    ['/agent-network/commissions', 'agent-network.commissions', 'Kelola commission rules (Personal / Level / Group)'],
                    ['/agent-network/network',     'agent-network.network',     'Visualisasi hierarki aktor'],
                    ['/agent-network/dashboard',   'agent-network.dashboard',   'Overview stats'],
                    ['/agent-network/transactions','agent-network.transactions','Log transaksi'],
                    ['/agent-network/payouts',     'agent-network.payouts',     'Antrian komisi pending → proses payout'],
                ] as [$path, $name, $desc])
                <tr style="border-bottom:1px solid rgb(229 231 235)">
                    <td style="padding:8px 12px">
                        <a href="{{ route('$name') }}" style="color:rgb(99 102 241);font-family:'JetBrains Mono',monospace;text-decoration:none">{{ $path }}</a>
                    </td>
                    <td style="padding:8px 12px;color:rgb(75 85 99)">{{ $desc }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    {{-- Not included --}}
    <div style="background:rgb(255 251 235);border:1px solid rgb(253 230 138);border-radius:8px;padding:20px">
        <div style="display:flex;align-items:center;gap:8px;margin-bottom:12px">
            <span class="ani sm" style="color:rgb(217 119 6)">info</span>
            <span style="font-size:13px;font-weight:600;color:rgb(180 83 9)">Tidak Termasuk (Implementasi Sendiri)</span>
        </div>
        <ul style="font-size:12px;color:rgb(92 71 17);margin:0;padding-left:16px;line-height:2">
            <li>Auth / middleware untuk route dashboard — tambahkan sendiri</li>
            <li>Transfer payout ke bank / dompet — implementasi di <code>ProcessPayoutBatch.php</code></li>
            <li>UI manajemen aktor — kelola dari app kamu sendiri</li>
            <li>Foreign key ke tabel <code>users</code> — tambahkan sendiri jika diperlukan</li>
        </ul>
    </div>

</div>
