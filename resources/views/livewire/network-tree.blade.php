<div>

{{-- Stats + Add button --}}
<div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:16px">
    <div style="display:grid;grid-template-columns:repeat(4,1fr);gap:12px;flex:1;margin-right:16px">
        <div style="background:#fff;border:1px solid rgb(229 231 235);border-radius:8px;padding:12px 14px">
            <div style="font-size:11px;font-weight:500;color:rgb(107 114 128);margin-bottom:4px">Total Actors</div>
            <div style="font-size:20px;font-weight:700;color:rgb(17 24 39)">{{ $stats['total'] ?? '—' }}</div>
        </div>
        <div style="background:#fff;border:1px solid rgb(229 231 235);border-radius:8px;padding:12px 14px">
            <div style="font-size:11px;font-weight:500;color:rgb(107 114 128);margin-bottom:4px">Active</div>
            <div style="font-size:20px;font-weight:700;color:rgb(17 24 39)">{{ $stats['active'] ?? '—' }}</div>
        </div>
        <div style="background:#fff;border:1px solid rgb(229 231 235);border-radius:8px;padding:12px 14px">
            <div style="font-size:11px;font-weight:500;color:rgb(107 114 128);margin-bottom:4px">Max Depth</div>
            <div style="font-size:20px;font-weight:700;color:rgb(17 24 39)">{{ $stats['depth'] ?? '—' }}</div>
        </div>
        <div style="background:#fff;border:1px solid rgb(229 231 235);border-radius:8px;padding:12px 14px">
            <div style="font-size:11px;font-weight:500;color:rgb(107 114 128);margin-bottom:4px">By Tier</div>
            @forelse($stats['types'] ?? [] as $type => $count)
            <div style="display:flex;justify-content:space-between;font-size:12px;color:rgb(55 65 81)">
                <span>{{ ucfirst($type) }}</span><span style="font-weight:600">{{ $count }}</span>
            </div>
            @empty
            <div style="font-size:13px;color:rgb(156 163 175)">—</div>
            @endforelse
        </div>
    </div>
    <button wire:click="openCreate" class="btn-primary" style="font-size:12px;padding:7px 14px;white-space:nowrap;flex-shrink:0">
        <span class="ani sm f">person_add</span> Add Actor
    </button>
</div>

{{-- Add / Edit Form --}}
@if($showForm)
<div style="background:#fff;border:1px solid var(--primary-400,#818cf8);border-radius:8px;margin-bottom:16px;overflow:hidden">
    <div style="padding:12px 16px;background:color-mix(in oklab,var(--primary-600,#6366f1) 5%,transparent);border-bottom:1px solid rgb(229 231 235);display:flex;align-items:center;justify-content:space-between">
        <span style="font-size:13px;font-weight:600;color:rgb(17 24 39)">
            {{ $formMode === 'create' ? 'Add Actor' : 'Edit Actor' }}
        </span>
        <button wire:click="cancelForm" class="btn-danger-sm"><span class="ani sm">close</span></button>
    </div>
    <div style="padding:16px;display:grid;grid-template-columns:1fr 1fr 1fr 1fr;gap:12px">
        <div>
            <label class="wz-label">Name <span style="color:rgb(239 68 68)">*</span></label>
            <input class="wz-input" type="text" wire:model="form.name" placeholder="Nama actor"/>
        </div>
        <div>
            <label class="wz-label">Tier <span style="color:rgb(239 68 68)">*</span></label>
            @if(!empty($entityTypes))
            <select class="wz-select" wire:model="form.type">
                <option value="">— pilih tier —</option>
                @foreach($entityTypes as $et)
                <option value="{{ $et }}">{{ ucfirst($et) }}</option>
                @endforeach
            </select>
            @else
            <input class="wz-input" type="text" wire:model="form.type" placeholder="contoh: reseller"/>
            @endif
        </div>
        <div>
            <label class="wz-label">Upline</label>
            <select class="wz-select" wire:model="form.parent_id">
                <option value="">— Root (tidak ada upline) —</option>
                @foreach($allNodes as $node)
                @if(($form['id'] ?? null) !== $node['id'])
                <option value="{{ $node['id'] }}">{{ $node['name'] }} · {{ ucfirst($node['type']) }}</option>
                @endif
                @endforeach
            </select>
        </div>
        <div>
            <label class="wz-label">Status</label>
            <select class="wz-select" wire:model="form.status">
                <option value="active">Active</option>
                <option value="inactive">Inactive</option>
            </select>
        </div>
    </div>
    <div style="padding:12px 16px;border-top:1px solid rgb(229 231 235);background:rgb(249 250 251);display:flex;justify-content:flex-end;gap:8px">
        <button wire:click="cancelForm" class="btn-ghost">Cancel</button>
        <button wire:click="saveActor" class="btn-primary"><span class="ani sm f">save</span> Save</button>
    </div>
</div>
@endif

{{-- Toolbar --}}
<div style="display:flex;align-items:center;gap:10px;margin-bottom:14px">
    <div style="position:relative;flex:1;max-width:300px">
        <span class="ani sm" style="position:absolute;left:10px;top:50%;transform:translateY(-50%);color:rgb(156 163 175);pointer-events:none">search</span>
        <input class="wz-input" type="text" wire:model.live.debounce.300ms="search"
               placeholder="Search actor..." style="padding-left:34px"/>
    </div>
    @if(!empty($allNodes))
    <button wire:click="expandAll" class="btn-ghost" style="font-size:12px;padding:7px 12px">
        <span class="ani sm">unfold_more</span> Expand All
    </button>
    <button wire:click="collapseAll" class="btn-ghost" style="font-size:12px;padding:7px 12px">
        <span class="ani sm">unfold_less</span> Collapse
    </button>
    @endif
</div>

{{-- Tree --}}
<div style="background:#fff;border:1px solid rgb(229 231 235);border-radius:8px;overflow:hidden">

    @if(empty($allNodes))
    <div style="padding:48px;text-align:center;color:rgb(156 163 175)">
        <span class="ani" style="font-size:28px;display:block;margin-bottom:8px">account_tree</span>
        <p style="font-size:13px;margin:0">No actors yet. Add the first actor to start building the network.</p>
    </div>

    @elseif($search)
        @forelse($filtered as $node)
        <div style="display:flex;align-items:center;gap:8px;padding:9px 16px;border-bottom:1px solid rgb(243 244 246)">
            <span class="ani sm" style="color:rgb(156 163 175);flex-shrink:0">person</span>
            <span style="font-size:13px;font-weight:500;color:rgb(17 24 39)">{{ $node['name'] }}</span>
            <span style="font-size:11px;background:rgb(243 244 246);color:rgb(75 85 99);padding:1px 7px;border-radius:4px">{{ ucfirst($node['type']) }}</span>
            <span style="font-size:11px;color:rgb(156 163 175);margin-left:auto">Depth {{ $node['depth'] }}</span>
            <button wire:click="openEdit({{ $node['id'] }})" class="btn-ghost" style="font-size:11px;padding:4px 8px">
                <span class="ani sm" style="font-size:13px">edit</span>
            </button>
        </div>
        @empty
        <div style="padding:32px;text-align:center;font-size:13px;color:rgb(156 163 175)">No results for "{{ $search }}"</div>
        @endforelse

    @else
        @php
        if (!function_exists('renderActorNode')) :
        function renderActorNode($node, $allNodes, $expanded) {
            $children    = collect($allNodes)->where('parent_id', $node['id'])->values();
            $hasChildren = $children->count() > 0;
            $isExpanded  = in_array($node['id'], $expanded);
            $pl          = ($node['depth'] * 20) + 16;
            $statusColor = $node['status'] === 'active' ? 'rgb(16 185 129)' : 'rgb(156 163 175)';

            $out  = '<div>';
            $out .= '<div style="display:flex;align-items:center;gap:8px;padding:9px 16px 9px '.$pl.'px;border-bottom:1px solid rgb(243 244 246)">';

            if ($hasChildren) {
                $out .= '<button wire:click="toggle('.$node['id'].')" style="width:18px;height:18px;border-radius:4px;background:rgb(243 244 246);border:1px solid rgb(229 231 235);cursor:pointer;display:inline-flex;align-items:center;justify-content:center;flex-shrink:0;padding:0;color:rgb(107 114 128)">';
                $out .= '<span class="ani" style="font-size:13px">'.($isExpanded ? 'expand_more' : 'chevron_right').'</span>';
                $out .= '</button>';
            } else {
                $out .= '<div style="width:18px;flex-shrink:0;display:flex;align-items:center;justify-content:center"><div style="width:4px;height:4px;border-radius:50%;background:rgb(209 213 219)"></div></div>';
            }

            $out .= '<span class="ani sm" style="color:rgb(156 163 175);flex-shrink:0">person</span>';
            $out .= '<div style="flex:1;min-width:0">';
            $out .= '<span style="font-size:13px;font-weight:500;color:rgb(17 24 39)">'.htmlspecialchars($node['name']).'</span>';
            $out .= '<span style="font-size:11px;background:rgb(243 244 246);color:rgb(75 85 99);padding:1px 7px;border-radius:4px;margin-left:8px">'.ucfirst($node['type']).'</span>';
            $out .= '</div>';
            $out .= '<span style="font-size:11px;color:'.$statusColor.';flex-shrink:0">'.ucfirst($node['status']).'</span>';
            $out .= '<button wire:click="toggleStatus('.$node['id'].')" style="margin-left:8px;font-size:11px;background:transparent;border:1px solid rgb(229 231 235);padding:2px 8px;border-radius:4px;cursor:pointer;color:rgb(107 114 128);flex-shrink:0">'.($node['status']==='active'?'Deactivate':'Activate').'</button>';
            $out .= '<button wire:click="openEdit('.$node['id'].')" style="margin-left:4px;background:transparent;border:1px solid rgb(229 231 235);padding:4px 6px;border-radius:4px;cursor:pointer;color:rgb(107 114 128);display:inline-flex;align-items:center;flex-shrink:0"><span class="ani sm" style="font-size:13px">edit</span></button>';
            $out .= '</div>';

            if ($hasChildren && $isExpanded) {
                foreach ($children as $child) {
                    $out .= renderActorNode($child, $allNodes, $expanded);
                }
            }

            $out .= '</div>';
            return $out;
        }
        endif;
        @endphp

        @foreach($roots as $rootId)
        @if(isset($allNodes[$rootId]))
        {!! renderActorNode($allNodes[$rootId], $allNodes, $expanded) !!}
        @endif
        @endforeach
    @endif

</div>

</div>
