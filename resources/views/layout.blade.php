<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ ($title ?? 'Agent Network') }} — Agent Network</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200"/>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=JetBrains+Mono:wght@400;500;600&display=swap"/>

    @filamentStyles
    @livewireStyles

    <style>
        .ani{font-family:'Material Symbols Outlined';font-variation-settings:'FILL' 0,'wght' 400,'GRAD' 0,'opsz' 20;font-size:18px;line-height:1;display:inline-flex;align-items:center;user-select:none;vertical-align:middle}
        .ani.f{font-variation-settings:'FILL' 1,'wght' 400,'GRAD' 0,'opsz' 20}
        .ani.lg{font-size:22px}
        .ani.sm{font-size:15px}

        body{margin:0;font-family:inherit}

        /* ── Sidebar ── */
        #an-sidebar{
            width:256px;flex-shrink:0;background:#fff;
            display:flex;flex-direction:column;
            position:fixed;top:0;left:0;height:100vh;z-index:40;
            border-right:1px solid rgb(229 231 235);
        }
        .an-brand{
            height:57px;padding:0 16px;
            display:flex;align-items:center;gap:10px;
            border-bottom:1px solid rgb(229 231 235);
        }
        .an-brand-icon{
            width:28px;height:28px;background:var(--primary-600,#6366f1);
            border-radius:6px;display:flex;align-items:center;justify-content:center;flex-shrink:0;
        }
        .an-brand-icon .ani{font-size:16px;color:#fff}
        .an-brand-name{font-size:13px;font-weight:600;color:rgb(17 24 39)}
        .an-brand-ver{font-size:10px;color:rgb(156 163 175);margin-top:1px}

        #an-nav{flex:1;padding:10px 8px;display:flex;flex-direction:column;gap:1px;overflow-y:auto}
        .an-nav-group{
            font-size:10px;font-weight:600;text-transform:uppercase;letter-spacing:.06em;
            color:rgb(156 163 175);padding:0 8px;margin:10px 0 4px;
        }
        .an-nav-group:first-child{margin-top:2px}
        .an-nav-item{
            display:flex;align-items:center;gap:9px;
            padding:7px 10px;border-radius:6px;
            font-size:13px;font-weight:500;color:rgb(107 114 128);
            text-decoration:none;transition:background 120ms,color 120ms;
        }
        .an-nav-item:hover{background:rgb(243 244 246);color:rgb(17 24 39)}
        .an-nav-item.active{background:color-mix(in oklab,var(--primary-600,#6366f1) 8%,transparent);color:var(--primary-700,#4338ca);font-weight:600}
        .an-nav-item .ani{font-size:16px;color:inherit;flex-shrink:0}

        #an-footer{
            padding:12px 16px;border-top:1px solid rgb(229 231 235);
            font-size:10.5px;color:rgb(156 163 175);
        }

        /* ── Main ── */
        #an-main{margin-left:256px;flex:1;display:flex;flex-direction:column;min-height:100vh;background:rgb(249 250 251)}
        #an-topbar{
            height:57px;border-bottom:1px solid rgb(229 231 235);background:#fff;
            display:flex;align-items:center;padding:0 24px;gap:8px;
            position:sticky;top:0;z-index:30;
        }
        #an-topbar h1{font-size:14px;font-weight:600;color:rgb(17 24 39);margin:0}
        #an-topbar .sep{color:rgb(209 213 219);font-size:12px;margin:0 2px}
        #an-content{padding:24px;flex:1}

        /* ── Form & component primitives ── */
        .wz-label{display:block;font-size:12px;font-weight:500;color:rgb(55 65 81);margin-bottom:5px}
        .wz-input{
            width:100%;border:1px solid rgb(209 213 219);border-radius:6px;
            padding:8px 12px;font-size:13.5px;color:rgb(17 24 39);background:#fff;
            outline:none;transition:border-color 150ms,box-shadow 150ms;box-sizing:border-box;
        }
        .wz-input:focus{border-color:var(--primary-500,#6366f1);box-shadow:0 0 0 3px color-mix(in oklab,var(--primary-500,#6366f1) 12%,transparent)}
        .wz-input:disabled{background:rgb(249 250 251);color:rgb(156 163 175);cursor:not-allowed}
        .wz-select{
            width:100%;appearance:none;border:1px solid rgb(209 213 219);border-radius:6px;
            padding:8px 32px 8px 12px;font-size:13.5px;color:rgb(17 24 39);background:#fff url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 24 24'%3E%3Cpath fill='%236b7280' d='M7 10l5 5 5-5z'/%3E%3C/svg%3E") no-repeat right 10px center;
            outline:none;cursor:pointer;transition:border-color 150ms;box-sizing:border-box;
        }
        .wz-select:focus{border-color:var(--primary-500,#6366f1);box-shadow:0 0 0 3px color-mix(in oklab,var(--primary-500,#6366f1) 12%,transparent)}
        .wz-hint{font-size:11.5px;color:rgb(107 114 128);margin-top:4px;line-height:1.4}

        .btn-primary{
            display:inline-flex;align-items:center;gap:6px;
            background:var(--primary-600,#6366f1);color:#fff;
            font-size:13px;font-weight:500;padding:8px 16px;
            border-radius:6px;border:none;cursor:pointer;
            transition:background 150ms;
        }
        .btn-primary:hover{background:var(--primary-700,#4f46e5)}
        .btn-ghost{
            display:inline-flex;align-items:center;gap:6px;
            background:#fff;color:rgb(55 65 81);
            font-size:13px;font-weight:500;padding:8px 14px;
            border-radius:6px;border:1px solid rgb(209 213 219);cursor:pointer;
            transition:background 150ms,border-color 150ms;
        }
        .btn-ghost:hover{background:rgb(249 250 251);border-color:rgb(156 163 175)}
        .btn-danger-sm{
            background:transparent;border:none;cursor:pointer;
            color:rgb(209 213 219);padding:4px;border-radius:4px;
            display:inline-flex;align-items:center;transition:color 150ms;
        }
        .btn-danger-sm:hover{color:rgb(239 68 68)}

        /* ── Toggle ── */
        .an-tog{width:36px;height:20px;border-radius:99px;background:rgb(209 213 219);position:relative;cursor:pointer;transition:background 150ms;flex-shrink:0}
        .an-tog.on{background:var(--primary-600,#6366f1)}
        .an-tog-thumb{position:absolute;top:2px;left:2px;width:16px;height:16px;border-radius:50%;background:#fff;box-shadow:0 1px 2px rgba(0,0,0,.15);transition:transform 150ms}
        .an-tog.on .an-tog-thumb{transform:translateX(16px)}

        /* ── Segmented control ── */
        .seg{display:flex;padding:2px;background:rgb(243 244 246);border:1px solid rgb(229 231 235);border-radius:6px;gap:2px}
        .seg-btn{flex:1;padding:6px 10px;font-size:12px;font-weight:500;border-radius:4px;text-align:center;color:rgb(107 114 128);cursor:pointer;border:none;background:transparent;transition:all 130ms}
        .seg-btn.on{background:#fff;color:rgb(17 24 39);font-weight:600;box-shadow:0 1px 2px rgba(0,0,0,.06)}

        /* ── Cards ── */
        .wz-card{background:#fff;border:1px solid rgb(229 231 235);border-radius:8px;overflow:hidden}
        .wz-card-hd{padding:16px 20px;border-bottom:1px solid rgb(229 231 235)}
        .wz-card-hd h2{font-size:15px;font-weight:600;color:rgb(17 24 39);margin:0 0 3px}
        .wz-card-hd p{font-size:12.5px;color:rgb(107 114 128);margin:0}
        .wz-card-bd{padding:20px}
        .wz-card-ft{padding:14px 20px;border-top:1px solid rgb(229 231 235);background:rgb(249 250 251);display:flex;justify-content:space-between;align-items:center}
        .cm-card{background:#fff;border:1px solid rgb(229 231 235);border-radius:8px;overflow:hidden}
        .cm-hd{display:flex;align-items:center;justify-content:space-between;padding:12px 16px;border-bottom:1px solid rgb(229 231 235);background:rgb(249 250 251)}
        .cm-hd-l{display:flex;align-items:center;gap:8px}
        .cm-hd h3{font-size:13px;font-weight:600;color:rgb(17 24 39);margin:0}
        .cm-body{padding:16px;display:flex;flex-direction:column;gap:12px}
        .cm-lbl{font-size:11px;font-weight:600;letter-spacing:.04em;text-transform:uppercase;color:rgb(107 114 128);margin-bottom:5px;display:block}

        /* ── Repeater add button ── */
        .rep-add{width:100%;padding:7px;border:1.5px dashed rgb(209 213 219);border-radius:6px;font-size:12px;font-weight:500;color:rgb(107 114 128);background:transparent;cursor:pointer;display:flex;align-items:center;justify-content:center;gap:5px;margin-top:6px;transition:all 150ms}
        .rep-add:hover{border-color:var(--primary-400,#818cf8);color:var(--primary-600,#6366f1)}

        /* ── Commission type toggle tile ── */
        .ct-tile{border:1px solid rgb(229 231 235);border-radius:8px;padding:12px 14px;transition:all 150ms;background:#fff}
        .ct-tile.on{border-color:color-mix(in oklab,var(--primary-600,#6366f1) 30%,transparent);background:color-mix(in oklab,var(--primary-600,#6366f1) 4%,transparent)}

        /* ── Entity rate card ── */
        .er-card{border:1px solid rgb(229 231 235);border-radius:8px;overflow:hidden;min-width:200px}
        .er-card-hd{padding:10px 14px;background:var(--primary-600,#6366f1)}
        .er-section{padding:10px 12px;border-bottom:1px solid rgb(243 244 246)}
        .er-section:last-child{border-bottom:none}
        .er-section-lbl{font-size:10px;font-weight:600;text-transform:uppercase;letter-spacing:.05em;color:rgb(107 114 128);margin-bottom:6px;display:flex;align-items:center;gap:4px}

        /* ── Info/warning box ── */
        .info-box{display:flex;gap:8px;padding:10px 12px;background:rgb(255 251 235);border:1px solid rgb(253 230 138);border-radius:6px}
        .info-box p{font-size:12px;color:rgb(120 53 15);line-height:1.5;margin:0}
        .lv-line{height:1px;background:rgb(229 231 235)}

        /* ── Table ── */
        .sim-th{padding:9px 14px;font-size:11px;font-weight:600;letter-spacing:.04em;text-transform:uppercase;color:rgb(107 114 128);border-bottom:1px solid rgb(229 231 235);background:rgb(249 250 251)}
        .sim-td{padding:10px 14px;font-size:13px;color:rgb(55 65 81);border-bottom:1px solid rgb(243 244 246)}
    </style>

    @stack('styles')
</head>
<body class="fi-body antialiased" style="background:rgb(249 250 251)">

<div style="display:flex;min-height:100vh">

    {{-- Sidebar --}}
    <aside id="an-sidebar">
        <div class="an-brand">
            <div class="an-brand-icon">
                <span class="ani f">account_tree</span>
            </div>
            <div>
                <div class="an-brand-name">Agent Network</div>
                <div class="an-brand-ver">v1.0</div>
            </div>
        </div>

        <nav id="an-nav">
            <div class="an-nav-group">Monitoring</div>

            <a href="{{ route('agent-network.dashboard') }}"
               class="an-nav-item {{ request()->routeIs('agent-network.dashboard') ? 'active' : '' }}">
                <span class="ani">space_dashboard</span> Dashboard
            </a>
            <a href="{{ route('agent-network.transactions') }}"
               class="an-nav-item {{ request()->routeIs('agent-network.transactions') ? 'active' : '' }}">
                <span class="ani">receipt_long</span> Transactions
            </a>
            <a href="{{ route('agent-network.payouts') }}"
               class="an-nav-item {{ request()->routeIs('agent-network.payouts') ? 'active' : '' }}">
                <span class="ani">schedule_send</span> Payout Queue
            </a>

            <div class="an-nav-group">Configuration</div>
            <a href="{{ route('agent-network.network') }}"
               class="an-nav-item {{ request()->routeIs('agent-network.network') ? 'active' : '' }}">
                <span class="ani">account_tree</span> Network
            </a>
            <a href="{{ route('agent-network.commissions') }}"
               class="an-nav-item {{ request()->routeIs('agent-network.commissions') ? 'active' : '' }}">
                <span class="ani">percent</span> Commission Rules
            </a>
<div class="an-nav-group">Setup</div>
            <a href="{{ route('agent-network.setup') }}"
               class="an-nav-item {{ request()->routeIs('agent-network.setup') ? 'active' : '' }}">
                <span class="ani">construction</span> Generator
            </a>
            <a href="{{ route('agent-network.guide') }}"
               class="an-nav-item {{ request()->routeIs('agent-network.guide') ? 'active' : '' }}">
                <span class="ani">menu_book</span> Usage Guide
            </a>
        </nav>

        <div id="an-footer">aldiazhar/laravel-agent-network</div>
    </aside>

    {{-- Main --}}
    <div id="an-main">
        <div id="an-topbar">
            <span style="font-size:12px;color:rgb(156 163 175)">Agent Network</span>
            <span class="sep">/</span>
            <h1>{{ $title ?? 'Agent Network' }}</h1>
        </div>
        <div id="an-content">
            {{ $slot }}
        </div>
    </div>

</div>

@livewire('notifications')
@filamentScripts
@livewireScripts
@stack('scripts')

</body>
</html>
