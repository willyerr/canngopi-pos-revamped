@php
    use App\Helpers\UtilityHelper;
@endphp
<div class="flex flex-col items-center gap-3 p-3 text-sm font-semibold border shadow">
    <img src="{{ asset($menuData['image'] ? 'storage/' . $menuData['image'] : 'images/image-placeholder.png') }}" alt="menu-image" class="w-32 h-32 object-contain" />
    <span class="truncate max-w-full text-center">{{ $menuData['name'] }}</span>
    <span class="text-green-600">{{ UtilityHelper::formatCurrency($menuData['price']) }}</span>
    <x-button text="Add Item" icon="plus" wire:click="itemClicked" sm />
</div>
