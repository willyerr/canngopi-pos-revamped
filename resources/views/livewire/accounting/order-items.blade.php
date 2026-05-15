<div class="flex flex-col gap-5">
    <x-loading />

    <x-date
        label="Select Start Date to Filter"
        format="DD MMMM YYYY"
        wire:model.live.debounce.500ms="filterDateStart"
    />

    <x-date
        label="Select End Date to Filter"
        format="DD MMMM YYYY"
        wire:model.live.debounce.500ms="filterDateEnd"
    />

    <div class="flex gap-3">
        <x-button icon="funnel" text="Show Filter" wire:click="updateOrderItemData" />
        <x-button icon="document-arrow-down" text="Download Report" wire:click="exportToExcel" />
    </div>

    <x-table :$headers :$rows>
        @interact('column_action', $row)
            <x-button.circle
                color="secondary"
                icon="eye"
                wire:click="openOrderDetail({{ $row['order_id'] }})"
                sm
            />
        @endinteract

        @interact('column_total_price', $row)
            {{ \App\Helpers\UtilityHelper::formatCurrency($row['total_price']) }}
        @endinteract

        @interact('column_revenue', $row)
            {{ \App\Helpers\UtilityHelper::formatCurrency($row['revenue']) }}
        @endinteract
    </x-table>

    <div class="flex justify-end gap-8 mt-4 font-semibold">
        <div>Total Revenue: {{ $totalRevenue }}</div>
        <div>Total Non-Revenue: {{ $totalNonRevenue }}</div>
    </div>
</div>
