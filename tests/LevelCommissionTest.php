<?php

namespace AgentNetwork\Tests;

use App\Models\AgentNetwork\CommissionLedger;
use PHPUnit\Framework\Attributes\Test;

/**
 * Level Commission — komisi ke rantai upline berdasarkan kedalaman hierarki.
 * Level 1 = parent langsung, Level 2 = grandparent, dst.
 * Formula: amount × (rate_per_level / 100) per level
 */
class LevelCommissionTest extends CommissionTestCase
{
    #[Test]
    public function L01_dua_upline_dapat_rate_berbeda(): void
    {
        $root     = $this->makeAgent('Root',     'distributor');
        $branch   = $this->makeAgent('Branch',   'agen',      $root);
        $reseller = $this->makeAgent('Reseller', 'reseller',  $branch);

        $this->makeRule('level', [
            'rates' => [
                ['level' => 1, 'rate' => 3],
                ['level' => 2, 'rate' => 1],
            ],
        ]);

        $this->fire($reseller, 1_000_000);

        $this->assertEquals(30_000.0, $this->totalFor($branch,   'level'));
        $this->assertEquals(10_000.0, $this->totalFor($root,     'level'));
        $this->assertEquals(0.0,      $this->totalFor($reseller, 'level'));
    }

    #[Test]
    public function L02_jumlah_level_dalam_rule_membatasi_kedalaman(): void
    {
        $d = $this->makeAgent('D', 'distributor');
        $c = $this->makeAgent('C', 'agen',      $d);
        $b = $this->makeAgent('B', 'agen',      $c);
        $a = $this->makeAgent('A', 'reseller',  $b);

        $this->makeRule('level', [
            'rates' => [
                ['level' => 1, 'rate' => 3],
                ['level' => 2, 'rate' => 2],
            ],
        ]);

        $this->fire($a, 1_000_000);

        $this->assertEquals(30_000.0, $this->totalFor($b, 'level'));
        $this->assertEquals(20_000.0, $this->totalFor($c, 'level'));
        $this->assertEquals(0.0,      $this->totalFor($d, 'level'));  // di luar batas rule
    }

    #[Test]
    public function L03_aktor_tanpa_parent_tidak_ada_level_commission(): void
    {
        $agent = $this->makeAgent('Solo Agent', 'agent');
        $this->makeRule('level', [
            'rates' => [['level' => 1, 'rate' => 3]],
        ]);

        $this->fire($agent, 1_000_000);

        $this->assertEquals(0, CommissionLedger::where('commission_type', 'level')->count());
    }

    #[Test]
    public function L04_tidak_ada_rule_tidak_ada_komisi(): void
    {
        $parent = $this->makeAgent('Parent', 'agen');
        $child  = $this->makeAgent('Child',  'reseller', $parent);

        $this->fire($child, 1_000_000);

        $this->assertEquals(0, CommissionLedger::where('commission_type', 'level')->count());
    }

    #[Test]
    public function L05_rate_0_tidak_buat_ledger(): void
    {
        $parent = $this->makeAgent('Parent', 'agen');
        $child  = $this->makeAgent('Child',  'reseller', $parent);

        $this->makeRule('level', [
            'rates' => [['level' => 1, 'rate' => 0]],
        ]);

        $this->fire($child, 1_000_000);

        $this->assertEquals(0, CommissionLedger::where('commission_type', 'level')->count());
    }

    #[Test]
    public function L06_ledger_menyimpan_nomor_level_yang_benar(): void
    {
        $root     = $this->makeAgent('Root',     'distributor');
        $branch   = $this->makeAgent('Branch',   'agen',      $root);
        $reseller = $this->makeAgent('Reseller', 'reseller',  $branch);

        $this->makeRule('level', [
            'rates' => [
                ['level' => 1, 'rate' => 3],
                ['level' => 2, 'rate' => 1],
            ],
        ]);

        $this->fire($reseller, 1_000_000);

        $l1 = CommissionLedger::where('agent_id', $branch->id)->where('commission_type', 'level')->first();
        $l2 = CommissionLedger::where('agent_id', $root->id)->where('commission_type', 'level')->first();

        $this->assertEquals(1, $l1->level);
        $this->assertEquals(2, $l2->level);
    }

    #[Test]
    public function L07_dua_transaksi_level_terakumulasi_di_upline(): void
    {
        $root     = $this->makeAgent('Root',     'distributor');
        $reseller = $this->makeAgent('Reseller', 'reseller', $root);

        $this->makeRule('level', ['rates' => [['level' => 1, 'rate' => 3]]]);

        $this->fire($reseller, 1_000_000);
        $this->fire($reseller, 500_000);

        // 30.000 + 15.000 = 45.000
        $this->assertEquals(45_000.0, $this->totalFor($root, 'level'));
        $this->assertEquals(2, CommissionLedger::where('commission_type', 'level')->count());
    }

    #[Test]
    public function L08_rule_spesifik_entity_type_tidak_berlaku_ke_tipe_lain(): void
    {
        $root     = $this->makeAgent('Root',     'distributor');
        $reseller = $this->makeAgent('Reseller', 'reseller', $root);
        $agen     = $this->makeAgent('Agen',     'agen',     $root);

        // Rule hanya berlaku jika actor bertipe 'reseller'
        $this->makeRule('level', ['rates' => [['level' => 1, 'rate' => 3]]], 'reseller');

        $this->fire($reseller, 1_000_000);
        $this->fire($agen,     1_000_000);

        $this->assertEquals(30_000.0, $this->totalFor($root, 'level'));
        $this->assertEquals(1, CommissionLedger::where('commission_type', 'level')->count());
    }

    #[Test]
    public function L09_hierarki_5_node_rule_3_level_hanya_3_upline_terdekat(): void
    {
        $a = $this->makeAgent('A', 'distributor');
        $b = $this->makeAgent('B', 'agen',      $a);
        $c = $this->makeAgent('C', 'agen',      $b);
        $d = $this->makeAgent('D', 'agen',      $c);
        $e = $this->makeAgent('E', 'reseller',  $d);

        $this->makeRule('level', [
            'rates' => [
                ['level' => 1, 'rate' => 3],
                ['level' => 2, 'rate' => 2],
                ['level' => 3, 'rate' => 1],
            ],
        ]);

        $this->fire($e, 1_000_000);

        $this->assertEquals(30_000.0, $this->totalFor($d, 'level'));
        $this->assertEquals(20_000.0, $this->totalFor($c, 'level'));
        $this->assertEquals(10_000.0, $this->totalFor($b, 'level'));
        $this->assertEquals(0.0,      $this->totalFor($a, 'level'));  // di luar batas rule
    }

    #[Test]
    public function L10_dua_anak_bertransaksi_parent_dapat_level_dua_kali(): void
    {
        $parent = $this->makeAgent('Parent', 'agen');
        $child1 = $this->makeAgent('Child1', 'reseller', $parent);
        $child2 = $this->makeAgent('Child2', 'reseller', $parent);

        $this->makeRule('level', ['rates' => [['level' => 1, 'rate' => 3]]]);

        $this->fire($child1, 1_000_000);
        $this->fire($child2, 500_000);

        // 30.000 + 15.000 = 45.000
        $this->assertEquals(45_000.0, $this->totalFor($parent, 'level'));
        $this->assertEquals(2, CommissionLedger::where('agent_id', $parent->id)
            ->where('commission_type', 'level')->count());
    }
}
