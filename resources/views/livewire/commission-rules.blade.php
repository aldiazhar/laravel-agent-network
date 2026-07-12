<div>

{{-- Tab bar --}}
<div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:16px">
    <div class="seg">
        @foreach(['personal'=>'Personal','level'=>'Level','group'=>'Group'] as $tab => $label)
        @php $count = count($rules[$tab] ?? []); @endphp
        <button class="seg-btn {{ $activeTab === $tab ? 'on' : '' }}" wire:click="$set('activeTab','{{ $tab }}')">
            {{ $label }}
            @if($count > 0)<span style="font-size:10px;background:{{ $activeTab === $tab ? 'rgb(229 231 235)' : 'rgb(209 213 219)' }};padding:1px 6px;border-radius:99px;margin-left:4px">{{ $count }}</span>@endif
        </button>
        @endforeach
    </div>
    <button wire:click="$set('showCreate',true)" class="btn-primary" style="font-size:12px;padding:7px 14px">
        <span class="ani sm f">add</span> Add Rule
    </button>
</div>

{{-- Create form --}}
@if($showCreate)
<div style="background:#fff;border:1px solid var(--primary-400,#818cf8);border-radius:8px;margin-bottom:16px;overflow:hidden">
    <div style="padding:12px 16px;background:color-mix(in oklab,var(--primary-600,#6366f1) 5%,transparent);border-bottom:1px solid rgb(229 231 235);display:flex;align-items:center;justify-content:space-between">
        <span style="font-size:13px;font-weight:600;color:rgb(17 24 39)">New {{ ucfirst($activeTab) }} Rule</span>
        <button wire:click="$set('showCreate',false)" class="btn-danger-sm"><span class="ani sm">close</span></button>
    </div>
    <div style="padding:16px">
        <div style="margin-bottom:14px">
            <label class="wz-label">Entity Type <span style="font-weight:400;color:rgb(156 163 175)">(kosong = berlaku global semua tipe)</span></label>
            <select class="wz-select" wire:model="createForm.entity_type">
                <option value="">Global — semua tipe</option>
                @foreach($entityTypes as $et)
                <option value="{{ $et }}">{{ ucfirst($et) }}</option>
                @endforeach
            </select>
        </div>

        @include('agent-network::livewire.partials.commission-form', ['form' => 'create', 'type' => $activeTab])

        <div style="display:flex;justify-content:flex-end;gap:8px;margin-top:14px">
            <button wire:click="$set('showCreate',false)" class="btn-ghost">Cancel</button>
            <button wire:click="saveCreate" class="btn-primary"><span class="ani sm f">save</span> Save</button>
        </div>
    </div>
</div>
@endif

{{-- Rules list --}}
@if(empty($tabRules))
<div style="background:#fff;border:1px solid rgb(229 231 235);border-radius:8px;padding:48px;text-align:center;color:rgb(156 163 175)">
    <span class="ani" style="font-size:28px;display:block;margin-bottom:8px">rule</span>
    <p style="font-size:13px;margin:0">No {{ $activeTab }} rules yet.</p>
    <p style="font-size:12px;margin:6px 0 0;color:rgb(209 213 219)">Click "Add Rule" to create the first one.</p>
</div>
@else
<div style="display:flex;flex-direction:column;gap:8px">
    @foreach($tabRules as $rule)
    @php $isEditing = $editingId === $rule['id']; @endphp
    <div style="background:#fff;border:1px solid {{ $isEditing ? 'var(--primary-400,#818cf8)' : 'rgb(229 231 235)' }};border-radius:8px;overflow:hidden">

        <div style="display:flex;align-items:center;gap:12px;padding:12px 16px">
            <div style="flex:1;min-width:0">
                <div style="display:flex;align-items:center;gap:8px">
                    @if($rule['entity_type'])
                    <span style="font-size:12px;font-weight:600;background:rgb(243 244 246);color:rgb(55 65 81);padding:2px 8px;border-radius:4px">{{ ucfirst($rule['entity_type']) }}</span>
                    @else
                    <span style="font-size:12px;background:rgb(249 250 251);color:rgb(156 163 175);padding:2px 8px;border-radius:4px;border:1px solid rgb(229 231 235)">Global</span>
                    @endif
                    @php
                    $conds = $rule['conditions'];
                    $summary = match($activeTab) {
                        'personal' => isset($conds['rate']) && $conds['rate'] !== '' ? $conds['rate'].'%' : '—',
                        'level'    => isset($conds['rates']) ? count($conds['rates']).' levels' : '—',
                        'group'    => isset($conds['rate']) && $conds['rate'] !== '' ? $conds['rate'].'%' : '—',
                        default    => '—',
                    };
                    @endphp
                    <span style="font-size:12px;color:rgb(107 114 128)">{{ $summary }}</span>
                </div>
            </div>

            <div class="an-tog {{ $rule['active'] ? 'on' : '' }}"
                 wire:click="toggleActive({{ $rule['id'] }}, {{ $rule['active'] ? 'true' : 'false' }})">
                <div class="an-tog-thumb"></div>
            </div>
            <button wire:click="{{ $isEditing ? 'cancelEdit' : 'startEdit('.$rule['id'].')' }}"
                    class="btn-ghost" style="padding:6px 12px;font-size:12px">
                <span class="ani sm">{{ $isEditing ? 'close' : 'edit' }}</span>
                {{ $isEditing ? 'Cancel' : 'Edit' }}
            </button>
            <button wire:click="deleteRule({{ $rule['id'] }})" class="btn-danger-sm"
                    onclick="return confirm('Delete this rule?')">
                <span class="ani sm">delete</span>
            </button>
        </div>

        @if($isEditing)
        <div style="padding:16px;border-top:1px solid rgb(229 231 235);background:rgb(249 250 251)">
            <div style="margin-bottom:14px">
                <label class="wz-label">Entity Type</label>
                <select class="wz-select" wire:model="editForm.entity_type">
                    <option value="">Global — semua tipe</option>
                    @foreach($entityTypes as $et)
                    <option value="{{ $et }}">{{ ucfirst($et) }}</option>
                    @endforeach
                </select>
            </div>
            @include('agent-network::livewire.partials.commission-form', ['form' => 'edit', 'type' => $activeTab])
            <div style="display:flex;justify-content:flex-end;gap:8px;margin-top:14px">
                <button wire:click="cancelEdit" class="btn-ghost">Cancel</button>
                <button wire:click="saveEdit" class="btn-primary"><span class="ani sm f">save</span> Save</button>
            </div>
        </div>
        @endif

    </div>
    @endforeach
</div>
@endif

</div>
