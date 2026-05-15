<div class="h-[calc(100%-110px)] flex flex-col gap-5 p-3 overflow-y-auto">
    <x-loading />
    
    {{-- ===== DESAIN MODAL INVOICE (STRUK KASIR) ===== --}}
    <x-modal title="Invoice Detail" wire="invoiceModalState" size="sm" z-index="z-40" center>
        @if(!empty($selectedInvoice))
            <div class="flex flex-col font-mono text-sm text-gray-800 px-2 py-4">
                
                <div class="text-center pb-4 border-b border-dashed border-gray-400">
                    <h2 class="font-bold text-2xl tracking-widest uppercase">INVOICE</h2>
                    <p class="text-xs text-gray-500 mt-1">Order #{{ $selectedInvoice['id'] }} • {{ $selectedInvoice['order_type'] }}</p>
                    <p class="text-xs text-gray-500">{{ \Carbon\Carbon::parse($selectedInvoice['created_at'])->format('d M Y, H:i') }}</p>
                </div>

                <div class="py-3 border-b border-dashed border-gray-400 text-xs space-y-1">
                    <div class="flex justify-between">
                        <span class="text-gray-500">Customer:</span>
                        <span class="font-bold uppercase">{{ $selectedInvoice['customer_name'] }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-500">Payment:</span>
                        <span class="font-semibold">{{ $selectedInvoice['payment_method'] ?? '-' }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-500">Status:</span>
                        <span class="font-bold {{ $selectedInvoice['status'] === 'Done' ? 'text-green-600' : 'text-orange-500' }} uppercase">
                            {{ $selectedInvoice['status'] }}
                        </span>
                    </div>
                </div>

                <div class="py-3 border-b border-dashed border-gray-400">
                    <p class="font-bold text-xs mb-2 text-gray-500">ORDER ITEMS:</p>
                    <div class="flex flex-col gap-3 max-h-[35vh] overflow-y-auto pr-2">
                        @foreach($selectedInvoice['items'] as $item)
                            <div class="flex flex-col text-xs">
                                <div class="flex justify-between font-bold">
                                    <span class="uppercase">{{ $item['name'] }}</span>
                                    <span>{{ number_format($item['total_price'], 0, ',', '.') }}</span>
                                </div>
                                <div class="text-gray-500 mt-0.5">
                                    @php 
                                        $unitPrice = ($item['quantity'] > 0) ? ($item['total_price'] / $item['quantity']) : 0; 
                                    @endphp
                                    {{ $item['quantity'] }} x {{ number_format($unitPrice, 0, ',', '.') }}
                                </div>
                                @if(isset($item['notes']) && $item['notes'] !== '')
                                    <span class="italic text-red-400 mt-0.5 text-[10px]">*Notes: {{ $item['notes'] }}</span>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>

                <div class="py-3 border-b border-dashed border-gray-400 text-xs space-y-1">
                    <div class="flex justify-between text-gray-600">
                        <span>Subtotal</span>
                        <span>{{ number_format($selectedInvoice['subtotal'], 0, ',', '.') }}</span>
                    </div>
                    @if(isset($selectedInvoice['discount_value']) && $selectedInvoice['discount_value'] > 0)
                        <div class="flex justify-between text-red-500 font-semibold italic">
                            <span>Disc ({{ $selectedInvoice['discount_name'] ?? 'Promo' }})</span>
                            <span>-{{ number_format($selectedInvoice['discount_value'], 0, ',', '.') }}</span>
                        </div>
                    @endif
                    <div class="flex justify-between font-black text-[15px] mt-2 pt-2 border-t border-dashed border-gray-400 text-gray-900">
                        <span>TOTAL</span>
                        <span>{{ number_format($selectedInvoice['grand_total'], 0, ',', '.') }}</span>
                    </div>
                </div>
                
                <div class="text-center pt-4 text-xs">
                    <p class="text-gray-500">Cashier: <span class="font-bold text-gray-800">{{ $selectedInvoice['cashier_name'] }}</span></p>
                    <p class="mt-2 text-gray-400 italic">--- Thank You ---</p>
                </div>

            </div>
        @endif
    </x-modal>
    {{-- =========================================== --}}


    {{-- ===== MODAL QRIS UNTUK PEMBAYARAN ===== --}}
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
                Total Tagihan: Rp {{ number_format($processingOrderTotal ?? 0, 0, ',', '.') }}
            </p>

            <x-button 
                text="Confirm Order"
                icon="check"
                wire:click="confirmQrisPayment"
            />
        </div>
    </x-modal>
    {{-- ========================================= --}}


    {{-- MENU TAB --}}
    <div class="flex justify-between items-center border-b">
        <div class="flex gap-6">
            <button wire:click="setTab('order-list')" 
                class="pb-2 px-1 text-sm font-bold uppercase transition-all duration-200 {{ $activeTab === 'order-list' ? 'border-b-4 border-red-600 text-red-600' : 'text-gray-400 hover:text-gray-600' }}">
                Order List
            </button>
            <button wire:click="setTab('open-bill')" 
                class="pb-2 px-1 text-sm font-bold uppercase transition-all duration-200 {{ $activeTab === 'open-bill' ? 'border-b-4 border-red-600 text-red-600' : 'text-gray-400 hover:text-gray-600' }}">
                Open Bill
            </button>
        </div>
        
        <div class="w-64">
            <x-date placeholder="Filter by Order Date" format="DD MMMM YYYY" wire:model.live.debounce.500ms="orderDate" />
        </div>
    </div>
    
    {{-- KONTEN CARD --}}
    @if(count($filteredOrders) > 0)
        <section class="grid grid-cols-5 gap-4">
            @foreach($filteredOrders as $order)
                {{-- Memanggil komponen kartu dengan data lengkap --}}
                <livewire:cashier.order-list-card :orderData="$order" wire:key="order-{{ $order['id'] }}" />
            @endforeach
        </section>
    @else
        <section class="h-[60vh] flex flex-col justify-center items-center opacity-70">
            <img src="{{ asset('images/no-data-found.jpg') }}" alt="no-data" class="w-48 h-48 mix-blend-multiply" />
            <span class="text-gray-500 font-semibold mt-2">No {{ $activeTab === 'open-bill' ? 'open bills' : 'completed orders' }} found</span>
        </section>
    @endif
</div>