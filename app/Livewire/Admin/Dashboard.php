<?php

namespace App\Livewire\Admin;

use Livewire\Attributes\Layout;
use Livewire\Component;

use TallStackUi\Traits\Interactions; 

use App\Services\StatisticService;
use App\Helpers\ToastHelper;
use App\Helpers\UtilityHelper;

use Carbon\Carbon;

class Dashboard extends Component
{
    use Interactions;

    private StatisticService $statisticService;
    private ToastHelper $toaster;

    public ?string $filterType = 'Daily';

    public ?string $filterDate = null;
    public ?string $filterRangeStart = null;
    public ?string $filterRangeEnd = null;
    public ?string $filterMonthYear = null;

    public array $statistics = [
        'Total Transaction' => [
            'icon' => 'shopping-bag',
            'value' => 0
        ],
        'Total Item Sold' => [
            'icon' => 'cube',
            'value' => 0
        ],
        'Total Revenue' => [
            'icon' => 'wallet',
            'value' => 0
        ],
        'Total Nett Sales' => [
            'icon' => 'banknotes',
            'value' => 0
        ],
        'Total Discount' => [
            'icon' => 'receipt-percent',
            'value' => 0
        ]
    ];

    public string $currentLayout = 'components.layouts.admin';

    public function mount()
    {
        $today = Carbon::today()->toDateString();

        $this->filterDate = $today;
        $this->filterRangeStart = $today;
        $this->filterRangeEnd = $today;
        $this->filterMonthYear = Carbon::now()->format('Y-m');

        $statData =$this->loadStatisticData($this->filterType, $this->filterDate);
        $this->setStatisticData($statData);
    }

    public function boot(StatisticService $statisticService)
    {
        $requestRoute = request()->route()->getName();
        if($requestRoute === 'admin.dashboard') $this->currentLayout = 'components.layouts.admin';
        else if($requestRoute) $this->currentLayout = 'components.layouts.accounting';

        $this->statisticService = $statisticService;
        $this->toaster = new ToastHelper($this->toast());
    }

    public function updatedFilterDate()
    {
        if(!$this->filterDate)
            return;

        $statData = $this->loadStatisticData($this->filterType, $this->filterDate);
        $this->setStatisticData($statData);
        $this->toaster->success('Daily statistic successfully updated');
    }

    public function updatedFilterRangeStart()
    {
        $this->updateWeeklyStatisticIfValid();
    }

    public function updatedFilterRangeEnd()
    {
        $this->updateWeeklyStatisticIfValid();
    }

    public function updatedFilterMonthYear()
    {
        if(!$this->filterMonthYear) 
            return;

        try 
        {
            $start = Carbon::createFromFormat('Y-m', $this->filterMonthYear)->startOfMonth();
            $end = $start->copy()->endOfMonth();
    
            $statData = $this->loadStatisticData($this->filterType, $start->toDateString(), $end->toDateString());
            $this->setStatisticData($statData);
            $this->toaster->success('Monthly statistic successfully updated');
        } 
        catch (\Exception $e) 
        {
            $this->toaster->error('Invalid month format: ' . $e->getMessage());
        }
    }

    private function updateWeeklyStatisticIfValid()
    {
        if (!$this->filterRangeStart || !$this->filterRangeEnd)
            return;

    
        try 
        {
            $start = Carbon::parse($this->filterRangeStart);
            $end = Carbon::parse($this->filterRangeEnd);
    
            if ($start->gt($end)) {
                $this->toaster->error('Start date must not be greater than end date');
                return;
            }
    
            $statData = $this->loadStatisticData($this->filterType, $start->toDateString(), $end->toDateString());
            $this->setStatisticData($statData);
            $this->toaster->success('Weekly statistic successfully updated');
        } 
        catch (\Exception $e) 
        {
            $this->toaster->error('Invalid date format');
        }
    }

    private function setStatisticData(array $statData)
    {
        $this->statistics['Total Transaction']['value'] = $statData['total_transactions'] ?? 0;
        $this->statistics['Total Item Sold']['value'] = $statData['total_item_sold'] ?? 0;
        $this->statistics['Total Revenue']['value'] = UtilityHelper::formatCurrency($statData['total_revenue'] ?? 0);
        $this->statistics['Total Nett Sales']['value'] = UtilityHelper::formatCurrency($statData['nett_sales'] ?? 0);
        $this->statistics['Total Discount']['value'] = UtilityHelper::formatCurrency($statData['total_discount'] ?? 0);
    }

    public function loadStatisticData(string $type, string $startDate, ?string $endDate = null)
    {
        try {
            $start = Carbon::parse($startDate);
            $end = $endDate ? Carbon::parse($endDate) : $start;
    
            switch ($type) {
                case 'Daily':
                    $end = $start->copy();
                    break;
    
                case 'Weekly':
                case 'Monthly':
                    break;
    
                default:
                    throw new \Exception("Unknown statistic type: $type");
            }
    
            return $this->statisticService->getData($start, $end);
        }
        catch(\Exception $e) {
            $this->handleError($e);
            return [];
        }
    }

    private function handleError(\Exception $e)
    {
        return $this->toaster->error($e->getMessage());
    }

    public function render()
    {
        return view('livewire.admin.dashboard')
            ->layout($this->currentLayout);
    }
}
