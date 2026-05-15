<div class="h-[250px] flex flex-col border shadow">
    <header class="h-[25px] flex flex-col justify-center items-center bg-primary text-white text-sm font-semibold">
        <span class="max-w-[90%] uppercase truncate">Order #{{ $orderData['id'] }} ({{ $orderData['order_type'] }}) - {{ $orderData['customer_name'] }}</span>
    </header>
    <section class="h-[calc(100%-65px)] flex flex-col gap-3 p-3 overflow-y-auto">
        @foreach($orderData['items'] as $item)
        <div class="flex flex-col text-[12px]">
            <div class="flex justify-between">
                <span class="max-w-[80%] truncate">{{ $item['name'] }}</span>
                <span>x{{ $item['quantity'] }}</span>
            </div>
            @if(isset($item['notes']))
                <span class="text-gray-400 text-[10px] font-semibold">Notes: {{ $item['notes'] }}</span>
            @endif
        </div>
        @endforeach
    </section>
    <div class="h-[40px] flex justify-center items-center border-t">
        @if($orderData['status'] === 'Pending')
            <span class="text-gray-500 text-[12px]">⚙️ Order In Progress</span>
        @else
            <span class="text-gray-500 text-[12px]">✔️ Order Completed</span>
        @endif
    </div>
</div>