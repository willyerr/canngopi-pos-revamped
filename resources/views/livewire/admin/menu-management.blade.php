@php
    use App\Helpers\UtilityHelper;
@endphp
<div class="flex flex-col gap-2">
    <x-modal :title="$modal['title']" wire="modal.state" center persistent z-index="z-40">
        <form class="flex flex-col gap-5" wire:submit="{{ $modal['type'] == 'create' ? 'create' : 'edit' }}">
            @if($modal['type'] == 'edit' && $imagePreview)
                <img src="{{ $imagePreview }}" alt="menu-image" width="200" class="object-cover" />
            @endif

            @foreach($modal['fields'] as $id => $field)
                @php
                    $formattedLabel = isset($field['nullable']) ? UtilityHelper::formatLabel($id) : UtilityHelper::formatLabel($id) . ' *';
                    $placeholderLabel = UtilityHelper::formatLabel($id);
                @endphp

                @if($field['type'] == 'text')
                    <x-input 
                        label="{{ $formattedLabel }}"
                        type="{{ $field['type'] }}" 
                        icon="{{ $field['icon'] }}" 
                        placeholder="Input {{ $placeholderLabel }}" 
                        wire:model="formData.{{ $id }}"
                    />
                @elseif($field['type'] == 'number')
                    <x-number 
                        label="{{ $formattedLabel }}"
                        hint="{{ $field['hint'] ?? '' }}"
                        min="{{ $field['min'] }}" 
                        wire:model="formData.{{ $id }}"
                    />
                @elseif($field['type'] == 'select')
                    <x-select.styled 
                        label="{{ $formattedLabel }}" 
                        :options="$field['options']" 
                        wire:model="formData.{{ $id }}"
                    />
                @elseif($field['type'] == 'file')
                    <x-upload 
                        label="Image" 
                        hint="Note: You can leave the image blank" 
                        accept="image/png, image/jpg"
                        wire:model="formData.{{ $id }}"
                    />
                @endif
            @endforeach
            <x-button type="submit" text="Save" icon="bookmark-square" />
        </form>
    </x-modal>
        
    <div class="flex justify-end">
        <x-button icon="plus" text="Create a New Menu" wire:click="toggleModal('create')" />
    </div>
    <x-table :headers="$headers" :rows="$rows" :quantity="[5, 10, 20]" striped filter loading paginate>
        @interact('column_action', $row) 
            <x-button.circle color="yellow" icon="pencil" wire:click="toggleModal('edit', {{ $row->id }})" sm />
            <x-button.circle color="red" icon="trash" wire:click="deleteConfirmation('{{ $row->id }}')" sm />
        @endinteract
    </x-table>
</div>