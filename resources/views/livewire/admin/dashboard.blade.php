<div class="flex flex-col gap-3">
    <div class="flex justify-between items-center gap-3">
        <x-select.styled
                label="Select Filter Type"
                placeholder="Filter Type"
                :options="['Daily', 'Weekly', 'Monthly']" 
                wire:model.live.debounce.500ms="filterType"
            />
        @if($filterType === 'Daily')
            <x-input type="date" label="Select Date" wire:key="daily" wire:model.live.debounce.500ms="filterDate" />
        @elseif($filterType === 'Weekly')
            <div class="flex gap-3">
                <x-input type="date" label="Start Date" wire:key="weeklyStart" wire:model.live.debounce.500ms="filterRangeStart" />
                <x-input type="date" label="End Date" wire:key="weeklyEnd" wire:model.live.debounce.500ms="filterRangeEnd" />
            </div>
        @elseif($filterType === 'Monthly')
            <x-date label="Select Month and Year" wire:key="monthly" wire:model.live.debounce.500ms="filterMonthYear" month-year-only />
        @endif
    </div>
    <div class="grid grid-cols-2 gap-3">
        @foreach($statistics as $id => $stat)
            <x-stats :number="$stat['value']" :title="$id" :icon="$stat['icon']" />
        @endforeach
    </div>
</div>
