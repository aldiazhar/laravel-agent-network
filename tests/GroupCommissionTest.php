<?php

namespace AgentNetwork\Tests;

use App\Models\AgentNetwork\CommissionLedger;
use PHPUnit\Framework\Attributes\Test;

/**
 * Group Commission — komisi ke parent langsung dari pelaku transaksi (satu level saja).
 * Formula: amount × (rate / 100)
 * Penerima: hanya actor.parent, tidak naik lebih jauh.
 */
class GroupCommissionTest extends CommissionTestCase
{
    #[Test]
    public function G01_rate_2_persen_ke_parent_langsung(): void
    {
        $parent = $this->makeAgent('Branch',   'agen');
        $child  = $this->makeAgent('Reseller', 'reseller', $parent);
        $this->makeRule('group', ['rate' => 2]);

        $this->fire($child, 1_000_000);

        $this->assertEquals(20_000.0, $this->totalFor($parent, 'group'));
        $this->assertEquals(0.0,      $this->totalFor($child,  'group'));
    }

    #[Test]
    public function G02_aktor_tanpa_parent_tidak_ada_group_commission(): void
    {
        $agent = $this->makeAgent('Solo', 'agent');
        $this->makeRule('group', ['rate' => 2]);

        $this->fire($agent, 1_000_000);

        $this->assertEquals(0, CommissionLedger::where('commission_type', 'group')->count());
    }

    #[Test]
    public function G03_tidak_ada_rule_tidak_ada_komisi(): void
    {
        $parent = $this->makeAgent('Parent', 'agen');
        $child  = $this->makeAgent('Child',  'reseller', $parent);

        $this->fire($child, 1_000_000);

        $this->assertEquals(0, CommissionLedger::where('commission_type', 'group')->count());
    }

    #[Test]
    public function G04_hanya_parent_langsung_yang_dapat_bukan_grandparent(): void
    {
        $root   = $this->makeAgent('Root',     'distributor');
        $branch = $this->makeAgent('Branch',   'agen',      $root);
        $leaf   = $this->makeAgent('Reseller', 'reseller',  $branch);
        $this->makeRule('group', ['rate' => 2]);

        $this->fire($leaf, 1_000_000);

        $this->assertEquals(20_000.0, $this->totalFor($branch, 'group'));
        $this->assertEquals(0.0,      $this->totalFor($root,   'group'));
    }

    #[Test]
    public function G05_transaksi_dari_dua_anak_terakumulasi_ke_parent(): void
    {
        $parent = $this->makeAgent('Branch',    'agen');
        $child1 = $this->makeAgent('Reseller1', 'reseller', $parent);
        $child2 = $this->makeAgent('Reseller2', 'reseller', $parent);
        $this->makeRule('group', ['rate' => 2]);

        $this->fire($child1, 1_000_000);
        $this->fire($child2, 500_000);

        $this->assertEquals(30_000.0, $this->totalFor($parent, 'group'));
        $this->assertEquals(2, CommissionLedger::where('agent_id', $parent->id)
            ->where('commission_type', 'group')->count());
    }

    #[Test]
    public function G06_rate_0_tidak_buat_ledger(): void
    {
        $parent = $this->makeAgent('Parent', 'agen');
        $child  = $this->makeAgent('Child',  'reseller', $parent);
        $this->makeRule('group', ['rate' => 0]);

        $this->fire($child, 1_000_000);

        $this->assertEquals(0, CommissionLedger::count());
    }

    #[Test]
    public function G07_rule_spesifik_entity_type_tidak_berlaku_ke_tipe_lain(): void
    {
        $parent   = $this->makeAgent('Parent',   'agen');
        $reseller = $this->makeAgent('Reseller', 'reseller', $parent);
        $agen     = $this->makeAgent('Agen',     'agen',     $parent);

        // Rule hanya berlaku jika actor bertipe 'reseller'
        $this->makeRule('group', ['rate' => 2], 'reseller');

        $this->fire($reseller, 1_000_000);
        $this->fire($agen,     1_000_000);

        $this->assertEquals(20_000.0, $this->totalFor($parent, 'group'));
        $this->assertEquals(1, CommissionLedger::where('commission_type', 'group')->count());
    }

    #[Test]
    public function G08_tiga_anak_bertransaksi_parent_dapat_tiga_ledger(): void
    {
        $parent = $this->makeAgent('Parent', 'agen');
        $c1     = $this->makeAgent('C1',     'reseller', $parent);
        $c2     = $this->makeAgent('C2',     'reseller', $parent);
        $c3     = $this->makeAgent('C3',     'reseller', $parent);
        $this->makeRule('group', ['rate' => 2]);

        $this->fire($c1, 1_000_000);
        $this->fire($c2, 800_000);
        $this->fire($c3, 200_000);

        // 20.000 + 16.000 + 4.000 = 40.000
        $this->assertEquals(40_000.0, $this->totalFor($parent, 'group'));
        $this->assertEquals(3, CommissionLedger::where('agent_id', $parent->id)
            ->where('commission_type', 'group')->count());
    }
}
