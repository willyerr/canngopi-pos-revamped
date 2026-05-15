<?php

namespace App\Livewire\Cashier;

use Livewire\Attributes\Layout;
use Livewire\Attributes\On; 
use Livewire\Attributes\Url;
use Livewire\Component;
use TallStackUi\Traits\Interactions; 
use App\Services\OrderManagementService;
use App\Helpers\ToastHelper;
use Carbon\Carbon;

class OrderList extends Component
{
    use Interactions;

    private OrderManagementService $orderManagementService;
    private ToastHelper $toaster;

    public array $orderData = [];
    public ?string $orderDate = null;
    
    // VARIABEL UNTUK TAB
    #[Url(as: 'tab')]
    public string $activeTab = 'order-list'; 

    // VARIABEL UNTUK MODAL INVOICE
    public bool $invoiceModalState = false;
    public array $selectedInvoice = [];

    // VARIABEL UNTUK MODAL QRIS (BANK TRANSFER)
    public bool $qrisModalState = false;
    public ?int $processingOrderId = null;
    public int $processingOrderTotal = 0;

    // VARIABEL UNTUK MENYIMPAN ID ORDER YANG DIPILIH UNTUK PEMBAYARAN
    public ?int $selectedOrderIdToPay = null;

    public function mount()
    {
        $this->orderDate = Carbon::today()->toDateString();
        $this->orderData = $this->loadOrderData()->toArray();
    }

    public function boot(OrderManagementService $orderManagementService)
    {
        $this->orderManagementService = $orderManagementService;
        $this->toaster = new ToastHelper($this->toast());
    }

    // FUNGSI UNTUK PINDAH TAB
    public function setTab($tabName)
    {
        $this->activeTab = $tabName;
    }

    public function updatedOrderDate()
    {
        if(is_null($this->orderDate))
            return;

        $this->orderData = $this->loadOrderData($this->orderDate)->toArray();
    }

    // FUNGSI UNTUK MENAMPILKAN INVOICE
    public function showInvoiceDetail(int $orderId)
    {
        $invoice = collect($this->orderData)->firstWhere('id', $orderId);

        if ($invoice) {
            $this->selectedInvoice = $invoice;
            $this->invoiceModalState = true; 
        } else {
            $this->toaster->error('Data pesanan tidak ditemukan');
        }
    }

    // FUNGSI UNTUK MEMUNCULKAN POPUP KONFIRMASI (DARI TOMBOL PAY)
    #[On('trigger-pay-confirmation')]
    public function requestPayConfirmation(int $orderId)
    {
        $this->selectedOrderIdToPay = $orderId;

        $this->dialog()
            ->success('Apakah Anda yakin?', 'Apakah Anda ingin melanjutkan pembayaran?')
            ->confirm('Ya, lanjutkan', 'processPayConfirmation')
            ->cancel('Tidak, batalkan')
            ->send();
    }

    // FUNGSI JIKA KASIR KLIK "YA" DI POPUP KONFIRMASI
    public function processPayConfirmation()
    {
        if ($this->selectedOrderIdToPay) {
            $this->onOrderFinished($this->selectedOrderIdToPay);
            $this->selectedOrderIdToPay = null;
        }
    }

    // FUNGSI EKSEKUSI ALUR PEMBAYARAN
    #[On('order-finished')]
    public function onOrderFinished(int $orderId)
    {
        $orderIndex = $this->findOrderIndex($orderId);
        
        if($orderIndex !== false && isset($this->orderData[$orderIndex])) {
            $order = $this->orderData[$orderIndex];
            
            // Jika payment method Bank Transfer (QRIS), munculkan Modal QRIS
            if ($order['payment_method'] === 'Bank Transfer' || $order['payment_method'] === 'QRIS') {
                $this->processingOrderId = $orderId;
                $this->processingOrderTotal = (int) $order['grand_total'];
                $this->qrisModalState = true; 
            } else {
                // Jika Cash / Lainnya, langsung selesaikan karena sudah dikonfirmasi di popup awal
                $this->completeOrder($orderId);
            }
        }
    }

    // FUNGSI SAAT KASIR MENEKAN "KONFIRMASI PEMBAYARAN" DI MODAL QRIS
    public function confirmQrisPayment()
    {
        if ($this->processingOrderId) {
            $this->completeOrder($this->processingOrderId);
            $this->qrisModalState = false;
            $this->processingOrderId = null;
        }
    }

    // FUNGSI UNTUK MENYELESAIKAN ORDER KE DATABASE
    public function completeOrder(int $orderId)
    {
        try
        {
            $updatedOrder = $this->orderManagementService->finishOrder($orderId);

            if($updatedOrder > 0) 
            {
                $orderIndex = $this->findOrderIndex($orderId);
                if($orderIndex !== false && isset($this->orderData[$orderIndex]))
                    $this->orderData[$orderIndex]['status'] = 'Done';
    
                $this->toaster->success('Order Completed');
            } 
            else $this->toaster->warning('Order not found or already completed');
        }
        catch(\Exception $e)
        {
            $this->handleError($e);
        }
    }

    // FUNGSI KETIKA TOMBOL KERANJANG DIKLIK
    public function addMenuToOpenBill(int $orderId)
    {
        return redirect()->route('cashier.create-order', ['order_id' => $orderId]);
    }

    private function findOrderIndex(int $orderId)
    {
        return array_search($orderId, array_column($this->orderData, 'id'));
    }

    public function loadOrderData()
    {
        try
        {
            $data = $this->orderManagementService->list($this->orderDate);
            $orders = $data->reverse()->values()->toArray();

            // === LOGIKA PENGGABUNGAN ITEM KEMBAR KHUSUS UNTUK TAMPILAN INVOICE ===
            foreach ($orders as &$order) {
                if (isset($order['items']) && is_array($order['items'])) {
                    $mergedItems = [];
                    
                    foreach ($order['items'] as $item) {
                        $key = $item['name']; // Kita kelompokkan berdasarkan nama menu
                        
                        if (isset($mergedItems[$key])) {
                            // Jika menu sudah ada di list, gabungkan Quantity dan Total Harganya
                            $mergedItems[$key]['quantity'] += $item['quantity'];
                            $mergedItems[$key]['total_price'] += $item['total_price'];
                            
                            // Gabungkan notes/catatan jika ada, dipisah dengan koma agar tidak hilang
                            if (!empty($item['notes'])) {
                                $mergedItems[$key]['notes'] = empty($mergedItems[$key]['notes']) 
                                    ? $item['notes'] 
                                    : $mergedItems[$key]['notes'] . ', ' . $item['notes'];
                            }
                        } else {
                            // Jika menu belum ada, masukkan sebagai baris baru
                            $mergedItems[$key] = $item;
                        }
                    }
                    
                    // Kembalikan ke format array bawaannya
                    $order['items'] = array_values($mergedItems);
                }
            }
            // =====================================================================

            return collect($orders);
        }
        catch(\Exception $e)
        {
            $this->orderData = [];
            $this->handleError($e);
            return collect([]);
        }
    }

    private function handleError(\Exception $e)
    {
        return $this->toaster->error($e->getMessage());
    }

    #[Layout('components.layouts.cashier')]
    public function render()
    {
        $filteredOrders = collect($this->orderData)->filter(function($order) {
            if ($this->activeTab === 'open-bill') {
                return $order['status'] === 'Pending';
            } else {
                return $order['status'] === 'Done';
            }
        })->values()->toArray();

        return view('livewire.cashier.order-list', [
            'filteredOrders' => $filteredOrders
        ]);
    }
}