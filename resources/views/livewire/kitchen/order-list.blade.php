<div class="h-[calc(100%-110px)] flex flex-col gap-5 p-3 overflow-y-auto">
    <x-button text="Reload Order" icon="arrow-path" wire:click="reloadOrder" />
    @if(count($orderData) > 0)
        <section class="grid grid-cols-5 gap-4">
            @foreach($orderData as $order)
                <livewire:kitchen.order-list-card :orderData="$order" wire:key="order-{{ $order['id'] }}" />
            @endforeach
        </section>
    @else
        <section class="h-full flex flex-col justify-center items-center">
            <img src="{{ asset('images/no-data-found.jpg') }}" alt="no-data" class="w-60 h-60" />
            <span class="text-lg font-semibold">No order found</span>
        </section>
    @endif
</div>