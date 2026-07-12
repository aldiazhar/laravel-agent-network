<?php

namespace AgentNetwork\Tests;

use App\Events\AgentNetwork\TransactionRegistered;
use App\Models\AgentNetwork\CommissionLedger;
use App\Models\AgentNetwork\Transaction;
use PHPUnit\Framework\Attributes\Test;

/**
 * Combined & Edge Cases — skenario multi-rule aktif, priority, rounding,
 * isolasi antar aktor, integritas ref, dan hierarki dalam.
 */
class CombinedCommissionTest extends CommissionTestCase
{
    #[Test]
    public function K01_semua_tipe_komisi_jalan_sekaligus(): void
    {
        $root     = $this->makeAgent('Root',     'distributor');
        $branch   = $this->makeAgent('Branch',   'agen',      $root);
        $reseller = $this->makeAgent('Reseller', 'reseller',  $branch);

        $this->makeRule('personal', ['rate' => 5]);
        $this->makeRule('level', [
            'rates' => [
                ['level' => 1, 'rate' => 3],
                ['level' => 2, 'rate' => 1],
            ],
        ]);
        $this->makeRule('group', ['rate' => 2]);

        $this->fire($reseller, 1_000_000);

        $this->assertEquals(50_000.0, $this->totalFor($reseller, 'personal'));
        $this->assertEquals(30_000.0, $this->totalFor($branch,   'level'));
        $this->assertEquals(10_000.0, $this->totalFor($root,     'level'));
        $this->assertEquals(20_000.0, $this->totalFor($branch,   'group'));
        $this->assertEquals(4, CommissionLedger::count());
    }

    #[Test]
    public function K02_total_komisi_terdistribusi_dari_1_transaksi_1juta(): void
    {
        $root     = $this->makeAgent('Root',     'distributor');
        $branch   = $this->makeAgent('Branch',   'agen',      $root);
        $reseller = $this->makeAgent('Reseller', 'reseller',  $branch);

        $this->makeRule('personal', ['rate' => 5]);
        $this->makeRule('level', [
            'rates' => [
                ['level' => 1, 'rate' => 3],
                ['level' => 2, 'rate' => 1],
            ],
        ]);
        $this->makeRule('group', ['rate' => 2]);

        $this->fire($reseller, 1_000_000);

        // Personal 50k + Level L1 30k + Level L2 10k + Group 20k = 110.000
        $this->assertEquals(110_000.0, (float) CommissionLedger::sum('amount'));
    }

    #[Test]
    public function K03_semua_ledger_berstatus_pending(): void
    {
        $branch   = $this->makeAgent('Branch',   'agen');
        $reseller = $this->makeAgent('Reseller', 'reseller', $branch);

        $this->makeRule('personal', ['rate' => 5]);
        $this->makeRule('level',    ['rates' => [['level' => 1, 'rate' => 3]]]);
        $this->makeRule('group',    ['rate' => 2]);

        $this->fire($reseller, 1_000_000);

        $this->assertEquals(0, CommissionLedger::where('status', '!=', 'pending')->count());
    }

    #[Test]
    public function K04_rule_spesifik_entity_type_lebih_prioritas_dari_global(): void
    {
        $reseller = $this->makeAgent('Reseller', 'reseller');

        $this->makeRule('personal', ['rate' => 3], null);         // global
        $this->makeRule('personal', ['rate' => 5], 'reseller');   // spesifik

        $this->fire($reseller, 1_000_000);

        // Pakai specific rule (5%), bukan global (3%)
        $this->assertEquals(50_000.0, $this->totalFor($reseller, 'personal'));
        $this->assertEquals(1, CommissionLedger::where('commission_type', 'personal')->count());
    }

    #[Test]
    public function K05_rounding_desimal_dua_angka(): void
    {
        $agent = $this->makeAgent('Agent', 'agent');
        $this->makeRule('personal', ['rate' => 2.5]);

        $this->fire($agent, 333_333);

        // 2.5% dari 333.333 = 8.333,325 → dibulatkan jadi 8.333,33
        $this->assertEquals(8_333.33, $this->totalFor($agent, 'personal'));
    }

    #[Test]
    public function K06_hierarki_4_level_dalam_dengan_3_level_rule(): void
    {
        $l1 = $this->makeAgent('L1', 'distributor');
        $l2 = $this->makeAgent('L2', 'agen',      $l1);
        $l3 = $this->makeAgent('L3', 'agen',      $l2);
        $l4 = $this->makeAgent('L4', 'reseller',  $l3);

        $this->makeRule('level', [
            'rates' => [
                ['level' => 1, 'rate' => 3],
                ['level' => 2, 'rate' => 2],
                ['level' => 3, 'rate' => 1],
            ],
        ]);

        $this->fire($l4, 1_000_000);

        $this->assertEquals(30_000.0, $this->totalFor($l3, 'level'));
        $this->assertEquals(20_000.0, $this->totalFor($l2, 'level'));
        $this->assertEquals(10_000.0, $this->totalFor($l1, 'level'));
        $this->assertEquals(0.0,      $this->totalFor($l4, 'level'));
    }

    #[Test]
    public function K07_transaksi_amount_nol_tidak_ada_ledger_sama_sekali(): void
    {
        $parent = $this->makeAgent('Parent', 'agen');
        $actor  = $this->makeAgent('Actor',  'reseller', $parent);

        $this->makeRule('personal', ['rate' => 5]);
        $this->makeRule('level',    ['rates' => [['level' => 1, 'rate' => 3]]]);
        $this->makeRule('group',    ['rate' => 2]);

        $this->fire($actor, 0);

        $this->assertEquals(0, CommissionLedger::count());
    }

    #[Test]
    public function K08_ledger_ref_id_dan_ref_type_menunjuk_ke_transaksi_yang_benar(): void
    {
        $agent = $this->makeAgent('Agent', 'agent');
        $this->makeRule('personal', ['rate' => 5]);

        $transaction = Transaction::create(['agent_id' => $agent->id, 'amount' => 1_000_000]);
        TransactionRegistered::dispatch($agent, $transaction);

        $ledger = CommissionLedger::first();
        $this->assertEquals($transaction->id,        $ledger->ref_id);
        $this->assertEquals(get_class($transaction), $ledger->ref_type);
    }

    #[Test]
    public function K09_dua_aktor_berbeda_personal_tidak_tercampur(): void
    {
        $a1 = $this->makeAgent('Actor1', 'agent');
        $a2 = $this->makeAgent('Actor2', 'agent');
        $this->makeRule('personal', ['rate' => 5]);

        $this->fire($a1, 1_000_000);
        $this->fire($a2, 600_000);

        $this->assertEquals(50_000.0, $this->totalFor($a1, 'personal'));
        $this->assertEquals(30_000.0, $this->totalFor($a2, 'personal'));
        $this->assertEquals(2, CommissionLedger::count());
    }

    #[Test]
    public function K10_aktor_tengah_dapat_personal_sendiri_dan_level_dari_anaknya(): void
    {
        $root   = $this->makeAgent('Root',   'distributor');
        $middle = $this->makeAgent('Middle', 'agen',     $root);
        $leaf   = $this->makeAgent('Leaf',   'reseller', $middle);

        $this->makeRule('personal', ['rate' => 5]);
        $this->makeRule('level',    ['rates' => [['level' => 1, 'rate' => 3]]]);

        $this->fire($middle, 1_000_000);  // personal → middle 50k, level L1 → root 30k
        $this->fire($leaf,   2_000_000);  // personal → leaf 100k, level L1 → middle 60k

        $this->assertEquals(50_000.0, $this->totalFor($middle, 'personal'));
        $this->assertEquals(60_000.0, $this->totalFor($middle, 'level'));
        $this->assertEquals(30_000.0, $this->totalFor($root,   'level'));
    }

    #[Test]
    public function K11_hanya_personal_rule_aktif_tidak_ada_level_group_ledger(): void
    {
        $root     = $this->makeAgent('Root',     'distributor');
        $branch   = $this->makeAgent('Branch',   'agen',      $root);
        $reseller = $this->makeAgent('Reseller', 'reseller',  $branch);

        $this->makeRule('personal', ['rate' => 5]);

        $this->fire($reseller, 1_000_000);

        $this->assertEquals(1, CommissionLedger::count());
        $this->assertEquals(0, CommissionLedger::where('commission_type', 'level')->count());
        $this->assertEquals(0, CommissionLedger::where('commission_type', 'group')->count());
    }

    #[Test]
    public function K12_dua_transaksi_ledger_memiliki_ref_id_yang_berbeda(): void
    {
        $agent = $this->makeAgent('Agent', 'agent');
        $this->makeRule('personal', ['rate' => 5]);

        $tx1 = Transaction::create(['agent_id' => $agent->id, 'amount' => 1_000_000]);
        TransactionRegistered::dispatch($agent, $tx1);

        $tx2 = Transaction::create(['agent_id' => $agent->id, 'amount' => 500_000]);
        TransactionRegistered::dispatch($agent, $tx2);

        $ledger1 = CommissionLedger::where('ref_id', $tx1->id)->first();
        $ledger2 = CommissionLedger::where('ref_id', $tx2->id)->first();

        $this->assertNotNull($ledger1);
        $this->assertNotNull($ledger2);
        $this->assertNotEquals($ledger1->ref_id, $ledger2->ref_id);
    }
}
