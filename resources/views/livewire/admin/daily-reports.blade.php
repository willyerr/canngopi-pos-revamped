<div class="flex flex-col gap-5">
    <x-loading />

    {{-- MODAL 1: ORDER DETAIL ITEM (Detail per Order) --}}
    <x-modal
        title="Order Item Detail"
        wire="orderDetailModal.state"
        size="xl"
        z-index="z-50"
        center
        persistent
    >
        <x-table
            :headers="$orderDetailModal['tableHeaders']"
            :rows="$orderDetailModal['data']"
            striped
        />
    </x-modal>

    {{-- MODAL 2: POP-UP STATISTIK (DARI KARTU) --}}
    <x-modal
        title="Detail Data: {{ $activeStat }}"
        wire="showFilterModal"
        size="6xl"
        z-index="z-40"
        center
        blur
        persistent
    >
        <div class="p-2">
            {{-- Bagian Ringkasan Menu (Item Summary) --}}
            @if(!empty($summaryItems))
                <div class="mb-5 bg-blue-50 border border-blue-200 rounded-lg p-4">
                    <h4 class="font-bold text-blue-800 mb-3 flex items-center gap-2">
                        <x-icon name="clipboard-document-list" class="w-5 h-5"/>
                        Item Summary (Most Sold)
                    </h4>
                    
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                        @foreach($summaryItems as $name => $qty)
                            <div class="bg-white p-2 rounded shadow-sm border border-blue-100 flex justify-between items-center">
                                <span class="text-sm text-gray-700 truncate font-medium" title="{{ $name }}">
                                    {{ $name }}
                                </span>
                                <span class="text-sm font-bold text-blue-600 bg-blue-100 px-2 py-0.5 rounded-full">
                                    {{ $qty }}
                                </span>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            {{-- Tabel Data di Dalam Modal --}}
            <div wire:key="table-wrapper-{{ $activeStat }}">
                <x-table :$headers :$rows>
                    @interact('column_action', $row)
                        <x-button.circle
                            color="secondary"
                            icon="eye"
                            wire:click="openOrderDetail({{ $row['id'] }})"
                            sm
                        />
                    @endinteract
                </x-table>
            </div>

            @if(collect($rows)->isEmpty())
                <div class="text-center p-4 text-gray-500 bg-gray-50 rounded mt-2">
                    No data found for category: <strong>{{ $activeStat }}</strong>.
                    <br><span class="text-xs text-gray-400">Please check the date filter.</span>
                </div>
            @endif
        </div>
        
        <x-slot:footer>
            <div class="flex justify-end">
                <x-button color="red" text="Close" x-on:click="$wire.showFilterModal = false" />
            </div>
        </x-slot:footer>
    </x-modal>

    {{-- UI DASHBOARD UTAMA --}}
<div class="w-full">
    <table style="width: 100%; border-spacing: 12px; border-collapse: separate; table-layout: fixed;">
        {{-- BARIS 1 --}}
        <tr>
            <td>
                <div wire:click="filterByStat('Total Transaction')" class="cursor-pointer transition hover:scale-[1.02]">
                    <x-stats :number="$statistics['Total Transaction']['value']" title="Total Transaction" :icon="$statistics['Total Transaction']['icon']" />
                </div>
            </td>
            <td>
                <div wire:click="filterByStat('Total Item Sold')" class="cursor-pointer transition hover:scale-[1.02]">
                    <x-stats :number="$statistics['Total Item Sold']['value']" title="Total Item Sold" :icon="$statistics['Total Item Sold']['icon']" />
                </div>
            </td>
            <td>
                <div wire:click="filterByStat('Total Revenue')" class="cursor-pointer transition hover:scale-[1.02]">
                    <x-stats :number="$statistics['Total Revenue']['value']" title="Total Revenue" :icon="$statistics['Total Revenue']['icon']" />
                </div>
            </td>
        </tr>

        {{-- BARIS 2 --}}
        <tr>
            <td>
                <div wire:click="filterByStat('Total Nett Sales')" class="cursor-pointer transition hover:scale-[1.02]">
                    <x-stats :number="$statistics['Total Nett Sales']['value']" title="Total Nett Sales" :icon="$statistics['Total Nett Sales']['icon']" />
                </div>
            </td>
            <td>
                <div wire:click="filterByStat('Total Discount')" class="cursor-pointer transition hover:scale-[1.02]">
                    <x-stats :number="$statistics['Total Discount']['value']" title="Total Discount" :icon="$statistics['Total Discount']['icon']" />
                </div>
            </td>
            <td>
                <div wire:click="filterByStat('Total Item Staff Meal')" class="cursor-pointer transition hover:scale-[1.02]">
                    <x-stats :number="$statistics['Total Item Staff Meal']['value']" title="Total Item Staff Meal" :icon="$statistics['Total Item Staff Meal']['icon']" />
                </div>
            </td>
        </tr>

        {{-- BARIS 3 (QC & Entertain tetap di kiri, kolom 3 kosong) --}}
        <tr>
            <td>
                <div wire:click="filterByStat('Total Item QC')" class="cursor-pointer transition hover:scale-[1.02]">
                    <x-stats :number="$statistics['Total Item QC']['value']" title="Total Item QC" :icon="$statistics['Total Item QC']['icon']" />
                </div>
            </td>
            <td>
                <div wire:click="filterByStat('Total Item Entertainment')" class="cursor-pointer transition hover:scale-[1.02]">
                    <x-stats :number="$statistics['Total Item Entertainment']['value']" title="Total Item Entertainment" :icon="$statistics['Total Item Entertainment']['icon']" />
                </div>
            </td>
            <td>
                {{-- Kosong supaya proporsional --}}
            </td>
        </tr>
    </table>
</div>

    {{-- FILTER TANGGAL --}}
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 bg-white p-4 rounded-lg shadow-sm border">
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

        <div class="md:col-span-2 flex gap-2">
            <x-button
                icon="funnel"
                text="Show Filter"
                wire:click="updateDailyStatistic" 
                class="flex-1"
            />

            <x-button
                icon="document-arrow-down"
                text="Download Report"
                wire:click="exportToExcel"
                color="red"
                outline
            />
        </div>
    </div>

    {{-- TABLE UTAMA --}}
    <div class="mt-4 bg-white p-4 rounded-lg shadow-sm border">
        <h3 class="font-bold text-lg mb-4 text-gray-700 flex items-center gap-2">
            <x-icon name="list-bullet" class="w-5 h-5"/>
            All Transactions List
        </h3>
        
        <x-table :$headers :$rows>
            @interact('column_action', $row)
                <x-button.circle
                    color="secondary"
                    icon="eye"
                    wire:click="openOrderDetail({{ $row['id'] }})"
                    sm
                />
            @endinteract
        </x-table>
        
        @if(collect($rows)->isEmpty())
            <div class="text-center p-8 text-gray-500 border border-dashed rounded-lg mt-2">
                <x-icon name="no-symbol" class="w-10 h-10 mx-auto mb-2 opacity-20"/>
                No transactions found for the selected date.
            </div>
        @endif
    </div>
</div>