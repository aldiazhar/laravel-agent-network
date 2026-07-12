{{-- $form = 'create' | 'edit', $type = 'personal'|'level'|'group' --}}
@php $f = $form === 'create' ? $createForm : $editForm; @endphp

@if($type === 'personal')
<div style="max-width:200px">
    <label class="wz-label">Rate (%)</label>
    <input class="wz-input" type="number" step="0.01" wire:model="{{ $form }}Form.conditions.rate" placeholder="5"/>
    <p class="wz-hint">Persentase dari nilai transaksi yang diterima pelaku transaksi.</p>
</div>
@endif

@if($type === 'level')
<div style="margin-bottom:8px">
    <label class="wz-label">Rate Per Level</label>
    <p class="wz-hint" style="margin:0 0 8px">Level 1 = parent langsung, Level 2 = grandparent, dst.</p>
    <div style="display:flex;flex-direction:column;gap:6px">
        @foreach($f['conditions']['rates'] ?? [] as $li => $lr)
        <div style="display:flex;align-items:center;gap:8px">
            <span style="font-size:12px;color:rgb(107 114 128);width:52px;flex-shrink:0">Level {{ $lr['level'] ?? $li+1 }}</span>
            <input class="wz-input" type="number" step="0.01"
                   wire:model="{{ $form }}Form.conditions.rates.{{ $li }}.rate" placeholder="Rate %"/>
            <button wire:click="removeLevelRate('{{ $form }}', {{ $li }})" class="btn-danger-sm">
                <span class="ani sm">remove</span>
            </button>
        </div>
        @endforeach
    </div>
    <button wire:click="addLevelRate('{{ $form }}')" class="rep-add" style="margin-top:6px">
        <span class="ani sm">add</span> Add Level
    </button>
</div>
@endif

@if($type === 'group')
<div style="max-width:200px">
    <label class="wz-label">Rate (%)</label>
    <input class="wz-input" type="number" step="0.01" wire:model="{{ $form }}Form.conditions.rate" placeholder="2"/>
    <p class="wz-hint">Persentase dari nilai transaksi yang diterima parent langsung dari pelaku.</p>
</div>
@endif
