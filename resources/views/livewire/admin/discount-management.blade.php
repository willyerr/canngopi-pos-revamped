@php
    use App\Helpers\UtilityHelper;
@endphp
<div class="flex flex-col gap-2">
    <x-modal :title="$modal['title']" wire="modal.state" center persistent z-index="z-40">
        <form class="flex flex-col gap-5" wire:submit="{{ $modal['type'] == 'create' ? 'create' : 'edit' }}">
            @foreach($modal['fields'] as $id => $field)
                @if($modal['type'] == 'edit' && $id == 'password')
                    @continue
                @endif

                {{-- SKIP render standar untuk minimum_purchase karena akan digabung dengan offer_value --}}
                @if($id == 'minimum_purchase')
                    @continue
                @endif

                @php
                    $formattedLabel = isset($field['nullable']) ? UtilityHelper::formatLabel($id) : UtilityHelper::formatLabel($id) . ' *';
                    $placeholderLabel = UtilityHelper::formatLabel($id);
                @endphp

                {{-- CUSTOM RENDER: Tipe Diskon (Radio Buttons) --}}
                @if($id == 'type')
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">{{ $formattedLabel }}</label>
                        <div class="flex gap-4">
                            <label class="inline-flex items-center cursor-pointer">
                                <input type="radio" wire:model.live="formData.type" value="percentage" class="form-radio text-red-600 w-4 h-4">
                                <span class="ml-2 text-sm text-gray-700">Percentage (%)</span>
                            </label>
                            <label class="inline-flex items-center cursor-pointer">
                                <input type="radio" wire:model.live="formData.type" value="nominal" class="form-radio text-red-600 w-4 h-4">
                                <span class="ml-2 text-sm text-gray-700">Nominal (Rp)</span>
                            </label>
                        </div>
                    </div>

                {{-- CUSTOM RENDER: Offer Value & Minimum Purchase (Selalu Muncul) --}}
                @elseif($id == 'offer_value')
                    <div class="grid grid-cols-2 gap-4">
                        {{-- Kolom Kiri: Input Nilai Diskon (Berubah sesuai tipe) --}}
                        @if(isset($formData['type']) && $formData['type'] === 'nominal')
                            <x-input 
                                label="Nominal Discount (Rp) *"
                                type="number" 
                                hint="Contoh: 25000" 
                                wire:model="formData.offer_value"
                            />
                        @else
                            <x-number 
                                label="Offer Value (%) *"
                                hint="Note: Percentage value can only in range of 1 - 100"
                                min="1" 
                                max="100"
                                wire:model="formData.offer_value"
                            />
                        @endif

                        {{-- Kolom Kanan: Input Minimal Belanja (Selalu muncul) --}}
                        <x-input 
                            label="Minimum Purchase (Rp) *"
                            type="number" 
                            hint="Isi 0 jika tanpa minimal" 
                            wire:model="formData.minimum_purchase"
                        />
                    </div>

                {{-- STANDARD RENDER: Untuk text, number biasa, textarea, select --}}
                @elseif($field['type'] == 'text')
                    <x-input 
                        label="{{ $formattedLabel }}"
                        type="{{ $field['type'] }}" 
                        icon="{{ $field['icon'] ?? '' }}" 
                        placeholder="Input {{ $placeholderLabel }}" 
                        wire:model="formData.{{ $id }}"
                    />
                @elseif($field['type'] == 'number')
                    <x-number 
                        label="{{ $formattedLabel }}"
                        hint="{{ $field['hint'] ?? '' }}"
                        min="{{ $field['min'] ?? 0 }}" 
                        wire:model="formData.{{ $id }}"
                    />
                @elseif($field['type'] == 'textarea')
                    <x-textarea 
                        label="{{ $formattedLabel }}"
                        resize-auto
                        wire:model="formData.{{ $id }}"
                    />
                @elseif($field['type'] == 'select' && isset($field['options']))
                    <x-select.native
                        label="{{ $formattedLabel }}"
                        :options="$field['options']"
                        wire:model="formData.{{ $id }}"
                    />
                @endif
            @endforeach
            
            <x-button type="submit" text="Save" icon="bookmark-square" />
        </form>
    </x-modal>

    <div class="flex justify-end">
        <x-button icon="plus" text="Create a New Discount" wire:click="toggleModal('create')" />
    </div>

    <x-table :headers="$headers" :rows="$rows" :quantity="[5, 10, 20]" striped filter loading paginate>
        @interact('column_action', $row) 
            <x-button.circle color="yellow" icon="pencil" wire:click="toggleModal('edit', {{ $row['id'] }})" sm />
            <x-button.circle color="red" icon="trash" wire:click="deleteConfirmation('{{ $row['id'] }}')" sm />
        @endinteract
    </x-table>
</div>