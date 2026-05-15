@php
    use App\Helpers\UtilityHelper;
@endphp

<div class="h-[calc(100%-110px)] flex">
    <x-loading /> 

    {{-- MODAL CHECKOUT --}}
<x-modal title="Checkout Order" wire="checkoutOrderModalState" size="md" z-index="z-40" center persistent>
    {{-- Menambahkan .prevent agar form tidak reload halaman, dan memanggil fungsi konfirmasi --}}
    <form class="flex flex-col gap-3" wire:submit.prevent="requestConfirmation">
            <x-input 
                label="Customer Name *" 
                icon="user" 
                placeholder="Input customer name" 
                wire:model="checkoutOrderData.customer_name" 
                required 
            />

            <x-number 
                label="Table Number *" 
                min="1" 
                wire:model="checkoutOrderData.table_number" 
                centralized 
                required 
            />

            <x-select.styled 
                label="Select Payment Method *"
                placeholder="Choose Payment Method"
                :options="[
                    'Bank Transfer', 
                    'Staff Meal', 
                    'Marketting Voucher',
                    'Entertainment',
                    'QC'
                ]" 
                wire:model.live.debounce.500ms="checkoutOrderData.payment_method"
                required
            />

            @if($checkoutOrderData['payment_method'] === 'Marketting Voucher')
                <x-number 
                    label="Voucher Quantity *" 
                    min="1" 
                    wire:model="checkoutOrderData.voucher_quantity" 
                    centralized 
                    required 
                />
            @endif

            <x-select.styled 
                label="Select Order Type *" 
                placeholder="Choose Order Type"
                :options="['Dine In', 'Take Away']" 
                wire:model.live.debounce.500ms="checkoutOrderData.order_type"
                required
            />

           {{-- MENU DROPDOWN OPEN BILL (Hanya muncul untuk metode pembayaran tertentu) --}}
            @if(!in_array($checkoutOrderData['payment_method'], ['Staff Meal', 'Entertainment', 'QC']))
                <x-select.styled 
                    label="Open Bill? *" 
                    placeholder="Choose bill method"
                    :options="['Yes', 'No']" 
                    wire:model.live="checkoutOrderData.is_open_bill"
                    required
                />
            @endif

            @if($checkoutOrderData['payment_method'] === 'Bank Transfer')
                <x-input 
                    type="email" 
                    label="Customer Email" 
                    icon="envelope" 
                    placeholder="Input customer email for invoice" 
                    wire:model="checkoutOrderData.customer_email" 
                    hint="Note: You can leave it blank" 
                />
            @endif

            <x-button type="submit" text="Proceed Order" icon="cog" />
        </form>
    </x-modal>

    {{-- MODAL QRIS BANK TRANSFER --}}
    <x-modal wire="qrisModalState" size="md" z-index="z-50" center persistent>
        <div class="flex flex-col items-center gap-4 pt-4">
            <h2 class="text-lg font-semibold">
                Pembayaran QRIS
            </h2>

            <img 
                src="{{ asset('images/qris-canngopi.jpeg') }}" 
                class="w-full border rounded"
                alt="QRIS Code"
            >

            <p class="text-base font-semibold">
                Total Tagihan: {{ UtilityHelper::formatCurrency($paymentRemainder) }}
            </p>
            
            <x-button 
                text="Confirm Order"
                icon="check"
                wire:click="confirmQrisPayment" />
        </div>
    </x-modal>

    {{-- MODAL ITEM DETAIL --}}
    <x-modal title="Order Item Detail" wire="orderItemDetailModalState" size="md" z-index="z-40" center persistent>
        <section class="flex flex-col gap-3">
            <div class="flex flex-col items-center gap-2 text-sm font-semibold">
                <img 
                    src="{{ asset($orderItemDetailData['image'] ? 'storage/' . $orderItemDetailData['image'] : 'images/image-placeholder.png') }}" 
                    alt="menu-image" 
                    class="w-32 h-32" 
                />
                <span class="text-center">{{ $orderItemDetailData['name'] }}</span>
                <span class="text-green-600">{{ UtilityHelper::formatCurrency($orderItemDetailData['price']) }}</span>
            </div>

            <x-number label="Quantity *" wire:model="orderItemDetailData.quantity" min="1" />

            <x-textarea 
                label="Note(s)" 
                hint="Note: you can leave the note blank" 
                wire:model="orderItemDetailData.notes" 
            />

            <x-button text="Add" icon="plus" wire:click="addItemToCart" />
        </section>
    </x-modal>

    {{-- SEBELAH KIRI: DAFTAR MENU --}}
    <section class="w-[75%] h-full p-3 overflow-y-auto">
        <section class="h-full flex flex-col gap-4">
            <x-input 
                icon="magnifying-glass" 
                position="right" 
                placeholder="Search menu name here..." 
                wire:model.live.debounce.500ms="searchMenuQuery" 
            />

            @if(count($menus) === 0)
                <div class="h-full flex flex-col justify-center items-center gap-3">
                    <x-icon name="book-open" class="w-20 h-20" />
                    <span>No menu found in database</span>
                </div>
            @else
                <div class="grid grid-cols-5 gap-4">
                    @foreach($menus as $menu)
                        <livewire:cashier.menu-card :menuData="$menu" wire:key="menu-{{ $menu['id'] }}" />
                    @endforeach
                </div>
            @endif
        </section>
    </section>

    {{-- SEBELAH KANAN: DAFTAR KERANJANG --}}
    <section class="w-[25%] h-full flex flex-col border">
        
        @if(count($cartItems) === 0)
            <div class="h-full flex flex-col justify-center items-center gap-3">
                <x-icon name="shopping-cart" class="w-12 h-12" />
                <span>No item added in cart</span>
            </div>
        @else
            <div class="h-full flex flex-col p-3 overflow-y-auto">
                @if($existingOrderId)
                    {{-- TAMPILAN MODE UPDATE (GROUPING) --}}
                    @php
                        $savedItems = collect($cartItems)->filter(fn($item) => !empty($item['is_saved']));
                        $newItems = collect($cartItems)->filter(fn($item) => empty($item['is_saved']));
                    @endphp

                    @if($savedItems->count() > 0)
                        <div class="mb-2 pb-1 border-b-2 border-dashed border-gray-300">
                            <span class="text-xs font-bold text-gray-500 uppercase tracking-wider flex items-center gap-1">
    <x-icon name="clock" class="w-4 h-4" />Pesanan Terdahulu</span>
                        </div>
                        <div class="flex flex-col gap-3 mb-4">
                            @foreach($savedItems as $id => $item)
                                <livewire:cashier.order-item-card :itemData="$item" wire:key="saved-{{ $id }}" />
                            @endforeach
                        </div>
                    @endif

                    @if($newItems->count() > 0)
                        <div class="mb-2 pb-1 border-b-2 border-dashed border-blue-300">
                            <span class="text-xs font-bold text-blue-600 uppercase tracking-wider flex items-center gap-1">
    <x-icon name="plus-circle" class="w-4 h-4 text-blue-600" />Tambahan Baru</span>
                        </div>
                        <div class="flex flex-col gap-3">
                            @foreach($newItems as $id => $item)
                                <livewire:cashier.order-item-card :itemData="$item" wire:key="new-{{ $id }}" />
                            @endforeach
                        </div>
                    @endif
                @else
                    {{-- TAMPILAN MODE PESANAN BARU (STANDAR/ORIGINAL) --}}
                    <div class="flex flex-col gap-3">
                        @foreach($cartItems as $id => $item)
                            <livewire:cashier.order-item-card :itemData="$item" wire:key="regular-{{ $id }}" />
                        @endforeach
                    </div>
                @endif
            </div>
        @endif

        {{-- AREA BAWAH: TOTAL & TOMBOL CONFIRM/UPDATE --}}
        <div class="h-fit flex flex-col justify-center gap-3 p-3 border">
            @if(count($cartItems) > 0)
                <div class="flex flex-col gap-3 text-sm">
                    <div class="flex justify-between">
                        <span>Subtotal</span>
                        <span>{{ UtilityHelper::formatCurrency($orderSummary['subtotal']) }}</span>
                    </div>

                    <div class="flex justify-between">
                        <span>Discount</span>
                        <span>{{ UtilityHelper::formatCurrency($orderSummary['discount']) }}</span>
                    </div>

                    <div class="flex justify-between">
                        <span class="font-bold">Grand Total</span>
                        <span class="font-bold">{{ UtilityHelper::formatCurrency($orderSummary['grand_total']) }}</span>
                    </div>
                </div>
            @endif

            <x-select.styled 
                :options="$discountOptions" 
                placeholder="Select discount here" 
                wire:model.live.debounce.500ms="selectedDiscountId" 
                :disabled="count($cartItems) === 0" 
            />

            @if($existingOrderId)
                {{-- TOMBOL UPDATE JIKA INI TAMBAHAN OPEN BILL --}}
                <x-button 
                    text="Update Pesanan" 
                    icon="arrow-path" 
                    color="green"
                    :disabled="count($cartItems) === 0" 
                    wire:click="requestUpdateConfirmation" {{-- KODE BARU --}}
                    />
            @else
                {{-- TOMBOL CONFIRM JIKA INI PESANAN BARU --}}
                <x-button 
                    text="Confirm Order" 
                    icon="check" 
                    :disabled="count($cartItems) === 0" 
                    wire:click="checkoutOrder" 
                />
            @endif
        </div>
    </section>
</div>