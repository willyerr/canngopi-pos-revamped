@php
    use App\Helpers\UtilityHelper;
@endphp

<div class="h-[calc(100%-110px)] flex flex-col gap-5 p-3 overflow-y-auto">
    <x-loading />
    <x-modal title="Confirm Order" wire="confirmOrderModalState" size="md" z-index="z-40" center persistent>
        <form class="flex flex-col gap-3" wire:submit="showTransactionConfirmDialog">
            <div class="flex flex-col gap-2">
                <div class="flex justify-between">
                    <span>Subtotal</span>
                    <span>{{ UtilityHelper::formatCurrency($selectedOrderSummary['subtotal']) }}</span>
                </div>
                <div class="flex justify-between">
                    <span>Discount</span>
                    <span>{{ UtilityHelper::formatCurrency($selectedOrderSummary['discount']) }}</span>
                </div>
                <div class="flex justify-between">
                    <span>Subtotal</span>
                    <span>{{ UtilityHelper::formatCurrency($selectedOrderSummary['grand_total']) }}</span>
                </div>
            </div>
            <x-select.styled :options="$discountOptions" placeholder="Select discount here" wire:model.live.debounce.500ms="selectedDiscountId" wire:loading wire:target="updatedSelectedDiscountId"  />
            <x-button type="submit" text="Proceed Order" icon="cog" />
        </form>
    </x-modal>
    @if(count($draftOrder) > 0)
        <section class="grid grid-cols-5 gap-4">
            @foreach($draftOrder as $order)
                <livewire:cashier.self-order-list-card :orderData="$order" wire:key="order-{{ $order['id'] }}" />
            @endforeach
        </section>
    @else
        <section class="h-full flex flex-col justify-center items-center">
            <img src="{{ asset('images/no-data-found.jpg') }}" alt="no-data" class="w-60 h-60" />
            <span class="text-lg font-semibold">No order found</span>
        </section>
    @endif
</div>
