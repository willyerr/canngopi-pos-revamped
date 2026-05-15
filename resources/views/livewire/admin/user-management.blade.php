<div class="flex flex-col gap-2">
    <x-modal :title="$modal['title']" wire="modal.state" center z-index="z-40">
        <form class="flex flex-col gap-5" wire:submit="{{ $modal['type'] == 'create' ? 'create' : 'edit' }}">
            @foreach($modal['fields'] as $id => $field)
                @if($modal['type'] == 'edit' && $id == 'password')
                    @continue
                @endif

                @php
                    $formattedLabel = ucfirst($id) . ' *';
                @endphp

                <x-input 
                    label="{{ $formattedLabel }}" 
                    type="{{ $field['type'] }}" 
                    icon="{{ $field['icon'] }}" 
                    placeholder="Input {{ $id }}" 
                    wire:model="formData.{{ $id }}"
                />
            @endforeach
            <x-select.native label="Role *" :options="['Admin', 'Cashier', 'Accounting', 'Kitchen', 'IT']" wire:model.change="formData.role" />
            <x-button type="submit" icon="bookmark-square" position="left" color="red">Save</x-button>
        </form>
    </x-modal>
    <div class="flex justify-end">
        <x-button icon="plus" text="Create a New User" wire:click="toggleModal('create')" />
    </div>
    <x-table :headers="$headers" :rows="$rows" :quantity="[5, 10, 20]" striped filter loading paginate>
        @interact('column_action', $row) 
            <x-button.circle color="yellow" icon="pencil" wire:click="toggleModal('edit', {{ $row->id }})" sm />
            <x-button.circle color="red" icon="trash" wire:click="deleteConfirmation('{{ $row->id }}')" sm />
        @endinteract
    </x-table>
</div>
