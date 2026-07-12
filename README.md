# Laravel Agent Network

Multi-level commission system generator for Laravel + Filament.

Generates migrations, models, events, listeners, and jobs directly into your app. Not a runtime library — generated code is yours to own and modify.

---

## Requirements

- PHP ^8.2
- Laravel ^11 | ^12 | ^13
- Filament ^4 | ^5

---

## Installation

```bash
composer require agentnetwork/laravel-agent-network
```

---

## Getting Started

**1. Open the setup wizard**

```
/agent-network/setup
```

Fill in network name, entity name, table prefix, and currency. Click **Generate Files**.

**2. Run migrations**

```bash
php artisan migrate
```

**3. Create commission rules**

```
/agent-network/commissions
```

**4. Call from your app whenever a transaction occurs**

```php
use AgentNetwork\Facades\AgentNetwork;

AgentNetwork::transact(actorId: $agent->id, amount: $order->total, ref: $order->id);
```

---

## Commission Types

### Personal — actor earns from their own transaction
```json
{ "rate": 5 }
```
```
Actor transacts 1,000,000 × 5% → Actor earns 50,000
```

### Level — upline chain earns based on depth
```json
{ "rates": [{ "level": 1, "rate": 3 }, { "level": 2, "rate": 1 }] }
```
```
Actor transacts 1,000,000:
  Parent      (level 1) earns 30,000
  Grandparent (level 2) earns 10,000
```

Depth is bounded by the number of entries in `rates`. Configure from the dashboard — no code changes needed.

### Group — direct parent earns per transaction
```json
{ "rate": 2 }
```
```
Actor transacts 1,000,000 × 2% → Direct parent earns 20,000
```

---

## Commission Rules

Rules are stored in the database and managed from `/agent-network/commissions`.

- `entity_type = null` → global, applies to all actor types
- `entity_type = 'reseller'` → applies only to actors of that type
- Specific rules always take priority over global rules
- One rule is applied per commission type per transaction

---

## Generated Files

```
database/migrations/
  create_{entity}s_table.php
  create_{prefix}commission_rules_table.php
  create_{prefix}commission_ledgers_table.php
  create_{prefix}transactions_table.php

app/Models/AgentNetwork/
  {Entity}.php            ← actor model (parent / children / ancestors)
  CommissionRule.php
  CommissionLedger.php
  Transaction.php

app/Events/AgentNetwork/
  TransactionRegistered.php
  CommissionEarned.php

app/Listeners/AgentNetwork/
  OnTransactionRegistered.php   ← commission logic
  OnCommissionEarned.php        ← empty hook (notifications, etc.)

app/Jobs/
  ProcessPayoutBatch.php        ← empty hook (bank/wallet transfers)

config/agent-network.php
```

---

## Dashboard

Standalone UI at `/agent-network/*`, separate from any Filament panel.

| Route | Description |
|---|---|
| `/agent-network/setup` | Generator wizard |
| `/agent-network/commissions` | Commission rules (Personal / Level / Group) |
| `/agent-network/network` | Actor hierarchy tree |
| `/agent-network/dashboard` | Overview stats |
| `/agent-network/transactions` | Transaction log |
| `/agent-network/payouts` | Pending commissions queue |

---

## Not Included

- Auth/middleware for dashboard routes — add your own
- Actual payout transfers — implement in `ProcessPayoutBatch.php`
- Actor management UI — manage actors from your own app
- Link to your `users` table — add the FK yourself if needed

---

## License

MIT
