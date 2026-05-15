@php
    use App\Helpers\UtilityHelper;
    $isSaved = !empty($itemData['is_saved']);
@endphp

{{-- 
    LOGIKA VISUAL:
    1. Jika isSaved (Menu Lama): Pakai BG Abu & Border Gray.
    2. Jika Pesanan Baru/Biasa: Pakai BG White & Border Standar (Tanpa Biru).
--}}
<div class="flex flex-col p-2 gap-3 text-sm border rounded shadow-sm {{ $isSaved ? 'bg-gray-100 border-gray-200' : 'bg-white border-gray-200' }}">
    <div class="flex flex-col">
        <div class="flex justify-between items-start">
            <span class="max-w-[80%] truncate font-semibold {{ $isSaved ? 'text-gray-500' : 'text-gray-900' }}">
                {{ $itemData['name'] }}
            </span>
            <span class="font-bold">x{{ $itemData['quantity'] }}</span>
        </div>
        
        @if(isset($itemData['notes']))
            <span class="text-gray-400 text-[11px] font-medium mt-1">Notes: {{ $itemData['notes'] }}</span>
        @endif
    </div>
    
    <div class="flex justify-between items-end mt-1">
        <span class="max-w-[60%] truncate font-medium">{{ UtilityHelper::formatCurrency($itemData['total_price']) }}</span>
        
        <div class="flex gap-2 items-center justify-end">
            @if($isSaved)
                {{-- Menu Lama: Kosong (Terkunci) --}}
                <div class="h-[30px]"></div>
            @else
                {{-- Menu Baru atau Pesanan Biasa: Muncul tombol Pencil & Trash secara SOLID --}}
                <x-button.circle icon="pencil" color="yellow" wire:click="editItem" class="shadow-none !opacity-100" sm />
                <x-button.circle icon="trash" wire:click="removeItem" color="red" class="shadow-none !opacity-100" sm />
            @endif
        </div>
    </div>
</div>