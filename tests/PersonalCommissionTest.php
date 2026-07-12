<?php

namespace AgentNetwork\Tests;

use App\Models\AgentNetwork\CommissionLedger;
use PHPUnit\Framework\Attributes\Test;

/**
 * Personal Commission — komisi langsung ke pelaku transaksi.
 * Formula: amount × (rate / 100)
 */
class PersonalCommissionTest extends CommissionTestCase
{
    #[Test]
    public function P01_rate_5_persen_dari_1juta(): void
    {
        $reseller = $this->makeAgent('Reseller A', 'reseller');
        $this->makeRule('personal', ['rate' => 5]);

        $this->fire($reseller, 1_000_000);

        $this->assertEquals(50_000.0, $this->totalFor($reseller, 'personal'));
        $this->assertEquals(1, CommissionLedger::where('commission_type', 'personal')->count());
    }

    #[Test]
    public function P02_rate_2_5_persen_dari_500ribu(): void
    {
        $agent = $this->makeAgent('Agent B', 'agent');
        $this->makeRule('personal', ['rate' => 2.5]);

        $this->fire($agent, 500_000);

        $this->assertEquals(12_500.0, $this->totalFor($agent, 'personal'));
    }

    #[Test]
    public function P03_tidak_ada_rule_tidak_ada_komisi(): void
    {
        $agent = $this->makeAgent('Agent C', 'agent');

        $this->fire($agent, 1_000_000);

        $this->assertEquals(0, CommissionLedger::where('commission_type', 'personal')->count());
    }

    #[Test]
    public function P04_rule_nonaktif_tidak_ada_komisi(): void
    {
        $agent = $this->makeAgent('Agent D', 'agent');
        CommissionLedger::query()->delete();

        \App\Models\AgentNetwork\CommissionRule::create([
            'commission_type' => 'personal',
            'active'          => false,
            'conditions'      => ['rate' => 5],
        ]);

        $this->fire($agent, 1_000_000);

        $this->assertEquals(0, CommissionLedger::where('commission_type', 'personal')->count());
    }

    #[Test]
    public function P05_rule_spesifik_entity_type_hanya_berlaku_ke_tipe_itu(): void
    {
        $reseller = $this->makeAgent('Reseller E', 'reseller');
        $agen     = $this->makeAgent('Agen E',     'agen');

        $this->makeRule('personal', ['rate' => 5], 'reseller');

        $this->fire($reseller, 1_000_000);
        $this->fire($agen,     1_000_000);

        $this->assertEquals(50_000.0, $this->totalFor($reseller, 'personal'));
        $this->assertEquals(0.0,      $this->totalFor($agen,     'personal'));
    }

    #[Test]
    public function P06_rule_global_berlaku_ke_semua_tipe(): void
    {
        $reseller = $this->makeAgent('Reseller F', 'reseller');
        $agen     = $this->makeAgent('Agen F',     'agen');

        $this->makeRule('personal', ['rate' => 3]);

        $this->fire($reseller, 1_000_000);
        $this->fire($agen,     1_000_000);

        $this->assertEquals(30_000.0, $this->totalFor($reseller, 'personal'));
        $this->assertEquals(30_000.0, $this->totalFor($agen,     'personal'));
    }

    #[Test]
    public function P07_tiga_transaksi_terakumulasi(): void
    {
        $agent = $this->makeAgent('Agent', 'agent');
        $this->makeRule('personal', ['rate' => 5]);

        $this->fire($agent, 1_000_000);
        $this->fire($agent, 500_000);
        $this->fire($agent, 200_000);

        // 50.000 + 25.000 + 10.000 = 85.000
        $this->assertEquals(85_000.0, $this->totalFor($agent, 'personal'));
        $this->assertEquals(3, CommissionLedger::where('commission_type', 'personal')->count());
    }

    #[Test]
    public function P08_rate_0_tidak_buat_ledger(): void
    {
        $agent = $this->makeAgent('Agent', 'agent');
        $this->makeRule('personal', ['rate' => 0]);

        $this->fire($agent, 1_000_000);

        $this->assertEquals(0, CommissionLedger::count());
    }

    #[Test]
    public function P09_dua_rule_global_aktif_hanya_satu_yang_dipakai(): void
    {
        $agent = $this->makeAgent('Agent', 'agent');
        $this->makeRule('personal', ['rate' => 5]);
        $this->makeRule('personal', ['rate' => 3]);

        $this->fire($agent, 1_000_000);

        // first() memilih satu rule — hanya 1 ledger dibuat, bukan 2
        $this->assertEquals(1, CommissionLedger::where('commission_type', 'personal')->count());
    }

    #[Test]
    public function P10_transaksi_kecil_100_rate_10_persen(): void
    {
        $agent = $this->makeAgent('Agent', 'agent');
        $this->makeRule('personal', ['rate' => 10]);

        $this->fire($agent, 100);

        $this->assertEquals(10.0, $this->totalFor($agent, 'personal'));
    }
}
