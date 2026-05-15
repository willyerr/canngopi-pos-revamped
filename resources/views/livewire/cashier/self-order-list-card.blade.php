<div class="h-[250px] flex flex-col border shadow">
    <header class="h-[25px] flex flex-col justify-center items-center bg-primary text-white text-sm font-semibold">
        <span class="max-w-[90%] uppercase truncate">{{ $orderData['customer_name'] }}</span>
    </header>
    <section class="h-[calc(100%-65px)] flex flex-col gap-3 p-3 overflow-y-auto">
        @foreach($orderData['items'] as $item)
        <div class="flex flex-col text-[12px]">
            <div class="flex justify-between">
                <span class="max-w-[80%] truncate">{{ $item['menu']['name'] }}</span>
                <span>x{{ $item['quantity'] }}</span>
            </div>
            @if(isset($item['notes']))
                <span class="text-gray-400 text-[10px] font-semibold">Notes: {{ $item['notes'] }}</span>
            @endif
        </div>
        @endforeach
    </section>
    <section class="h-[40px] flex justify-center items-center border-t gap-3">
        <x-button text="Confirm" icon="check" wire:click="showConfirmOrderModal" xs />
        <x-button text="Cancel" icon="x-mark" wire:click="showCancelConfirmDialog" xs />
    </section>
</div>
