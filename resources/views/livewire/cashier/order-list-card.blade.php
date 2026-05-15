@php
    use App\Helpers\UtilityHelper;
@endphp

{{-- Menambahkan efek hover dan cursor-pointer ke seluruh Card --}}
<div class="h-fit flex flex-col border shadow bg-white rounded-lg overflow-hidden cursor-pointer hover:shadow-xl hover:border-sky-400 hover:ring-1 hover:ring-sky-400 transition-all duration-200" 
     wire:click="$parent.showInvoiceDetail({{ $orderData['id'] }})">
    
    {{-- Header Kartu --}}
    <header class="h-[30px] flex flex-col justify-center items-center bg-red-600 text-white text-[11px] font-bold">
        <span class="max-w-[95%] uppercase truncate">
            ORDER #{{ $orderData['id'] ?? '-' }} ({{ $orderData['order_type'] ?? '' }}) - {{ $orderData['customer_name'] ?? '' }}
        </span>
    </header>

    {{-- Section Detail Item --}}
    <section class="max-h-[220px] flex flex-col gap-3 p-3 overflow-y-auto">
        @if(isset($orderData['items']) && is_array($orderData['items']))
            @foreach($orderData['items'] as $item)
            <div class="flex flex-col text-[12px] border-b border-gray-50 pb-2">
                <div class="flex justify-between font-bold text-gray-800">
                    <span class="max-w-[70%] uppercase truncate">{{ $item['name'] }}</span>
                    <span>{{ number_format($item['total_price'] ?? 0, 0, ',', '.') }}</span>
                </div>
                
                <div class="text-[10px] text-gray-500 mt-0.5 font-medium">
                    @php 
                        $unitPrice = ($item['quantity'] > 0) ? ($item['total_price'] / $item['quantity']) : 0; 
                    @endphp
                    {{ $item['quantity'] ?? 0 }} x {{ number_format($unitPrice, 0, ',', '.') }}
                </div>

                @if(isset($item['notes']) && $item['notes'] !== '')
                    <span class="text-red-400 text-[10px] italic mt-1 font-medium">Notes: {{ $item['notes'] }}</span>
                @endif
            </div>
            @endforeach
        @endif
    </section>

    {{-- Section Ringkasan Pembayaran --}}
    <section class="p-3 bg-gray-50 border-t border-dashed border-gray-300 space-y-1 text-[11px]">
        <div class="flex justify-between text-gray-600 font-medium">
            <span>Subtotal</span>
            <span>{{ number_format($orderData['subtotal'] ?? 0, 0, ',', '.') }}</span>
        </div>

        @if(isset($orderData['discount_value']) && $orderData['discount_value'] > 0)
        <div class="flex flex-col text-red-500 font-semibold italic">
            <div class="flex justify-between">
                <span>{{ $orderData['discount_name'] ?? 'Discount' }}</span>
                <span>-{{ number_format($orderData['discount_value'], 0, ',', '.') }}</span>
            </div>
        </div>
        @endif

        <div class="flex justify-between font-black text-[15px] pt-1 mt-1 border-t border-gray-900 text-gray-900 uppercase">
            <span>Total</span>
            <span class="text-red-600 font-bold">{{ number_format($orderData['grand_total'] ?? 0, 0, ',', '.') }}</span>
        </div>
    </section>

    {{-- Tombol Finish Order --}}
    {{-- WAJIB MENGGUNAKAN wire:click.stop AGAR KLIK FINISH TIDAK MEMBUKA INVOICE --}}
    {{-- Tombol Bawah (Keranjang & Pay) --}}
    <section class="h-[45px] flex justify-center items-center border-t bg-gray-50 px-2 gap-2">
        @if(($orderData['status'] ?? '') === 'Pending')

            {{-- Tombol Keranjang untuk nambah menu --}}
            <button wire:click.stop="$parent.addMenuToOpenBill({{ $orderData['id'] }})" 
                    class="flex-1 flex justify-center items-center h-[30px] rounded bg-yellow-400 text-white hover:bg-yellow-500 transition-colors shadow-sm">
                <x-icon name="shopping-cart" class="w-4 h-4" solid />
            </button>

            {{-- Tombol Pay (Finish Order) --}}
        <button wire:click.stop="$dispatch('trigger-pay-confirmation', { orderId: {{ $orderData['id'] }} })" 
            class="flex-[3] flex justify-center items-center h-[30px] rounded bg-red-600 text-white font-bold text-xs hover:bg-red-700 transition-colors shadow-sm uppercase tracking-wider">
        PAY
        </button>
        @else
            <div class="flex items-center gap-1 text-green-600 font-bold text-[11px] uppercase tracking-wider">
                <x-icon name="check-circle" class="w-4 h-4" solid />
                <span>Completed</span>
            </div>
        @endif
    </section>
</div>

