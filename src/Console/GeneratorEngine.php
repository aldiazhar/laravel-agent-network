<?php

namespace AgentNetwork\Console;

use Illuminate\Support\Str;

class GeneratorEngine
{
    protected array $ctx = [];

    public function __construct(protected array $data) {}

    public function run(): array
    {
        $this->buildContext();

        $written = [];
        $written = array_merge($written, $this->generateMigrations());
        $written = array_merge($written, $this->generateModels());
        $written = array_merge($written, $this->generateEvents());
        $written = array_merge($written, $this->generateListeners());
        $written = array_merge($written, $this->generateJobs());
        $written = array_merge($written, $this->generateConfig());

        return $written;
    }

    protected function buildContext(): void
    {
        $entity = strtolower($this->data['entity_name']);
        $prefix = rtrim($this->data['table_prefix'] ?? 'an', '_') . '_';
        $types  = collect($this->data['entity_types'] ?? [])
            ->pluck('name')
            ->filter()
            ->values();

        $entityTypesConfig = $types->mapWithKeys(fn ($t) => [
            $t => ['can_have_downline' => true],
        ])->all();

        foreach ($this->data['entity_types'] ?? [] as $typeRow) {
            $name = strtolower($typeRow['name'] ?? '');
            if ($name) {
                $entityTypesConfig[$name]['can_have_downline'] = (bool) ($typeRow['can_have_downline'] ?? true);
            }
        }

        $this->ctx = [
            'EntityName'       => ucfirst($entity),
            'EntityNames'      => ucfirst(Str::plural($entity)),
            'entityName'       => $entity,
            'entityNames'      => Str::plural($entity),
            'entity_table'     => $prefix . Str::plural($entity),
            'prefix'           => $prefix,
            'network_name'     => $this->data['network_name'] ?? 'Agent Network',
            'currency'         => $this->data['currency'] ?? 'IDR',
            'payout_schedule'  => $this->data['payout_schedule'] ?? 'monthly',
            'max_depth'        => $this->data['max_depth'] ?? 10,
            'entity_types_php' => $this->phpArray($entityTypesConfig),
        ];
    }

    protected function generateMigrations(): array
    {
        $written = [];

        $files = [
            ['migration/create_entity_table.stub',             "create_{$this->ctx['entity_table']}_table"],
            ['migration/create_commission_rules_table.stub',   "create_{$this->ctx['prefix']}commission_rules_table"],
            ['migration/create_commission_ledgers_table.stub', "create_{$this->ctx['prefix']}commission_ledgers_table"],
            ['migration/create_transactions_table.stub',       "create_{$this->ctx['prefix']}transactions_table"],
        ];

        foreach ($files as $i => [$stub, $name]) {
            $ts        = now()->addSeconds($i)->format('Y_m_d_His');
            $filename  = "{$ts}_{$name}.php";
            $dest      = database_path("migrations/{$filename}");
            $written[] = $this->writeStub($stub, $dest);
        }

        return $written;
    }

    protected function generateModels(): array
    {
        $written = [];
        $dir     = app_path('Models/AgentNetwork');

        $files = [
            ['model/Entity.stub',          "{$this->ctx['EntityName']}.php"],
            ['model/CommissionRule.stub',   'CommissionRule.php'],
            ['model/CommissionLedger.stub', 'CommissionLedger.php'],
            ['model/Transaction.stub',      'Transaction.php'],
        ];

        foreach ($files as [$stub, $filename]) {
            $written[] = $this->writeStub($stub, "{$dir}/{$filename}");
        }

        return $written;
    }

    protected function generateEvents(): array
    {
        $written = [];
        $dir     = app_path('Events/AgentNetwork');

        $stubs = [
            'event/TransactionRegistered.stub' => 'TransactionRegistered.php',
            'event/CommissionEarned.stub'       => 'CommissionEarned.php',
        ];

        foreach ($stubs as $stub => $filename) {
            $written[] = $this->writeStub($stub, "{$dir}/{$filename}");
        }

        return $written;
    }

    protected function generateListeners(): array
    {
        $written = [];
        $dir     = app_path('Listeners/AgentNetwork');

        $stubs = [
            'listener/OnTransactionRegistered.stub' => 'OnTransactionRegistered.php',
            'listener/OnCommissionEarned.stub'       => 'OnCommissionEarned.php',
        ];

        foreach ($stubs as $stub => $filename) {
            $written[] = $this->writeStub($stub, "{$dir}/{$filename}");
        }

        return $written;
    }

    protected function generateJobs(): array
    {
        $written = [];
        $dir     = app_path('Jobs/AgentNetwork');

        $stubs = [
            'job/ProcessPayoutBatch.stub' => 'ProcessPayoutBatch.php',
        ];

        foreach ($stubs as $stub => $filename) {
            $written[] = $this->writeStub($stub, "{$dir}/{$filename}");
        }

        return $written;
    }

    protected function generateConfig(): array
    {
        $dest = config_path('agent-network.php');
        return [$this->writeStub('config/agent-network.stub', $dest)];
    }

    protected function writeStub(string $stub, string $destination): string
    {
        $content = $this->render($stub);
        $dir     = dirname($destination);

        if (! is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        file_put_contents($destination, $content);

        return $destination;
    }

    protected function render(string $stub): string
    {
        $path    = __DIR__ . '/stubs/' . $stub;
        $content = file_get_contents($path);

        foreach ($this->ctx as $key => $value) {
            $content = str_replace("{{$key}}", is_bool($value) ? ($value ? 'true' : 'false') : $value, $content);
        }

        return $content;
    }

    protected function phpArray(array $arr): string
    {
        $lines = ["[\n"];
        foreach ($arr as $k => $v) {
            $canDownline = $v['can_have_downline'] ? 'true' : 'false';
            $lines[]     = "        '{$k}' => ['can_have_downline' => {$canDownline}],\n";
        }
        $lines[] = '    ]';
        return implode('', $lines);
    }
}
