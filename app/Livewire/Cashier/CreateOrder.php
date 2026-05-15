<?php

namespace App\Livewire\Cashier;

use Livewire\Attributes\Layout;
use Livewire\Attributes\On; 
use Livewire\Attributes\Url;
use Livewire\Component;
use TallStackUi\Traits\Interactions; 
use App\Services\MenuService;
use App\Services\DiscountService;
use App\Services\OrderManagementService;
use App\Helpers\ToastHelper;
use App\Helpers\UtilityHelper;

class CreateOrder extends Component
{
    use Interactions;

    private MenuService $menuService;
    private DiscountService $discountService;
    private OrderManagementService $orderManagementService;
    private ToastHelper $toaster;

    // VARIABEL UNTUK MENANGKAP ID ORDER DARI URL OPEN BILL
    #[Url(as: 'order_id')]
    public ?int $existingOrderId = null;

     // ===== TAMBAHAN QRIS & SISA BAYAR =====
    public $qrisModalState = false;
    public $paymentRemainder = 0; 
    // ======================================

    public bool $pageLoading = true;
    public bool $orderItemDetailModalState = false;
    
    public array $orderItemDetailData = [
        'id' => null, 'name' => '', 'price' => 0, 'image' => null,
        'quantity' => 1, 'category' => '', 'notes' => null,
        'is_saved' => false 
    ];

    public array $orderSummary = [
        'subtotal' => 0,
        'discount' => 0,
        'grand_total' => 0
    ];

    public bool $checkoutOrderModalState = false;

    public array $checkoutOrderData = [
        'customer_name' => null,
        'table_number' => null,
        'customer_email' => null,
        'payment_method' => null,
        'voucher_quantity' => null,
        'order_type' => null,
        'is_open_bill' => 'No', 
    ];

    public array $menus = [];
    public array $discounts = [];
    public array $cartItems = [];
    public array $discountOptions = [];

    public ?int $selectedDiscountId = null;
    public ?string $searchMenuQuery = null;

    public function boot(
        MenuService $menuService,
        DiscountService $discountService,
        OrderManagementService $orderManagementService
    ) {
        $this->menuService = $menuService;
        $this->discountService = $discountService;
        $this->orderManagementService = $orderManagementService;

        $this->toaster = new ToastHelper($this->toast());
    }

    public function mount()
    {
        $this->menus = $this->loadMenuData();
        $this->discounts = $this->loadDiscountData();

        // JIKA ADA ID DI URL, LOAD DATANYA KE KERANJANG
        if ($this->existingOrderId) {
            $this->loadExistingOrder();
        }
    }

   // FUNGSI BARU: Memasukkan data meja lama ke tampilan keranjang
    private function loadExistingOrder()
    {
        try {
            $order = $this->orderManagementService->getOrderById($this->existingOrderId);
            
            $this->checkoutOrderData = [
                'customer_name' => $order['customer_name'],
                'table_number' => $order['table_number'] ?? null,
                'customer_email' => $order['customer_email'],
                'payment_method' => $order['payment_method'],
                'voucher_quantity' => $order['voucher_quantity'] ?? null,
                'order_type' => $order['order_type'] ?? null,
                'is_open_bill' => $order['status'] === 'Pending' ? 'Yes' : 'No',
            ];

            // Masukkan kembali item lama ke cart
            foreach ($order['items'] as $item) {
                // Mencari gambar & id dari list menu utama
                $menu = collect($this->menus)->firstWhere('name', $item['name']);
                $menuId = $menu ? $menu['id'] : rand(10000, 99999);

                $this->cartItems[] = [
                    'id' => $menuId,
                    'name' => $item['name'],
                    'price' => $item['quantity'] > 0 ? $item['total_price'] / $item['quantity'] : 0,
                    'image' => $menu ? $menu['image'] : null,
                    'category' => $item['category'] ?? 'Uncategorized',
                    'quantity' => $item['quantity'],
                    'total_price' => $item['total_price'],
                    'notes' => $item['notes'],
                    'is_saved' => true 
                ];
            }

            // Pasang diskon jika sebelumnya pakai diskon
            if (!empty($order['discount_name'])) {
                $discount = collect($this->discounts)->first(function($d) use ($order) {
                    $haystack = $order['discount_name'] ?? '';
                    $needle = $d['name'] ?? '';
                    
                    if (empty($haystack) || empty($needle)) return false;
                    
                    return str_contains($haystack, $needle);
                });
                
                if ($discount) {
                    $this->selectedDiscountId = $discount['id'];
                }
            }

            $this->calculateOrderSummary();

        } catch (\Exception $e) {
            $this->toaster->error('Gagal memuat data pesanan: ' . $e->getMessage());
            $this->existingOrderId = null; // Reset jika gagal
        }
    }
    
    public function updated($property)
    {
        // === TAMBAHAN BARU: Reset Open Bill ke 'No' jika metode pembayaran internal dipilih ===
        if ($property === 'checkoutOrderData.payment_method') {
            if (in_array($this->checkoutOrderData['payment_method'], ['Staff Meal', 'Entertainment', 'QC'])) {
                $this->checkoutOrderData['is_open_bill'] = 'No';
            }
        }
        // =====================================================================================

        if ($property === 'selectedDiscountId') {
            $this->calculateOrderSummary();
        }

        if ($property === 'searchMenuQuery') {
            $this->menus = $this->loadMenuData(
                $this->searchMenuQuery === "" ? null : $this->searchMenuQuery
            );
        }
    }

    private function loadDiscountData()
    {
        try {
            $discountList = $this->discountService->list()->get()->toArray();
            $this->discountOptions = [];

            foreach ($discountList as $discount) {
                $label = $discount['name'];

                // Format angka awal (Persen atau Nominal)
                if (($discount['type'] ?? 'percentage') === 'percentage') {
                    $label .= ' (' . $discount['offer_value'] . '%';
                } else {
                    $label .= ' (Rp ' . number_format($discount['offer_value'], 0, ',', '.');
                }

                // Tambahkan info minimal belanja jika ada (> 0)
                $minPurchase = floatval($discount['minimum_purchase'] ?? 0);
                if ($minPurchase > 0) {
                    $label .= ' - Min. Rp ' . number_format($minPurchase, 0, ',', '.');
                }
                
                $label .= ')'; // Tutup kurung

                $this->discountOptions[] = [
                    'label' => $label,
                    'value' => $discount['id']
                ];
            }
            return $discountList;
        } catch (\Exception $e) {
            return [];
        }
    }

    #[On('item-clicked')]
    public function onItemClicked(array $itemData)
    {
        if ($this->findItemIndex($itemData['id']) !== false) {
            return $this->toaster->error('Item already in cart');
        }

        $this->orderItemDetailData = [
            'id' => $itemData['id'],
            'name' => $itemData['name'],
            'price' => $itemData['price'],
            'image' => $itemData['image'] ?? null,
            'category' => $itemData['category'],
            'quantity' => 1,
            'notes' => null,
            'is_saved' => false 
        ];

        $this->orderItemDetailModalState = true;
    }

    #[On('edit-item-clicked')]
    public function onEditItemClicked(int $itemId)
    {
        $index = $this->findItemIndex($itemId);

        if ($index === false) {
            return $this->toaster->error('Item not found');
        }

        $item = $this->cartItems[$index];

        $this->orderItemDetailData = [
            'id' => $item['id'],
            'name' => $item['name'],
            'price' => $item['price'],
            'image' => $item['image'] ?? null,
            'category' => $item['category'],
            'quantity' => $item['quantity'],
            'notes' => $item['notes'],
            'is_saved' => $item['is_saved'] ?? false 
        ];

        $this->orderItemDetailModalState = true;
    }

    #[On('remove-item-clicked')]
    public function onRemoveItemClicked(int $itemId)
    {
        $this->dialog()
            ->question('Remove item?', 'Are you sure?')
            ->confirm('Confirm', 'deleteItemFromCart', $itemId)
            ->send();
    }

    public function deleteItemFromCart(int $itemId)
    {
        $index = $this->findItemIndex($itemId);

        if ($index !== false) {

            unset($this->cartItems[$index]);

            $this->cartItems = array_values($this->cartItems);

            $this->calculateOrderSummary();
        }
    }

    public function addItemToCart()
    {
        $itemData = [
            'id' => $this->orderItemDetailData['id'],
            'name' => $this->orderItemDetailData['name'],
            'price' => $this->orderItemDetailData['price'],
            'image' => $this->orderItemDetailData['image'],
            'quantity' => $this->orderItemDetailData['quantity'],
            'total_price' => $this->orderItemDetailData['quantity']
                * $this->orderItemDetailData['price'],
            'category' => $this->orderItemDetailData['category'],
            'notes' => $this->orderItemDetailData['notes'],
           'is_saved' => $this->orderItemDetailData['is_saved'] ?? false 
        ];

        $index = $this->findItemIndex($itemData['id']);

        if ($index !== false) {
            $this->cartItems[$index] = $itemData;
        } else {
            $this->cartItems[] = $itemData;
        }

        $this->calculateOrderSummary();

        $this->orderItemDetailModalState = false;
    }

    private function findItemIndex(int $itemId)
    {
        foreach ($this->cartItems as $index => $item) {
            if ($item['id'] == $itemId && empty($item['is_saved'])) {
                return $index; 
            }
        }
        
        return false; 
    }

    private function calculateOrderSummary()
    {
        $this->orderSummary['subtotal'] = collect($this->cartItems)->sum('total_price');
        $discountValue = 0;

        if (!is_null($this->selectedDiscountId)) {
            $discount = collect($this->discounts)->firstWhere('id', $this->selectedDiscountId);

            if ($discount) {
                $minPurchase = floatval($discount['minimum_purchase'] ?? 0);
                
                // CEK MINIMAL BELANJA DULU (Berlaku untuk Persen maupun Nominal)
                if ($minPurchase > 0 && $this->orderSummary['subtotal'] < $minPurchase) {
                    $this->selectedDiscountId = null;
                    $this->toaster->error('Gagal apply diskon: Minimal belanja Rp ' . number_format($minPurchase, 0, ',', '.') . ' belum tercapai.');
                } else {
                    // JIKA SYARAT TERPENUHI, BARU HITUNG DISKONNYA
                    if (($discount['type'] ?? 'percentage') === 'percentage') {
                        $discountValue = $this->orderSummary['subtotal'] * ($discount['offer_value'] / 100);
                    } else {
                        $discountValue = floatval($discount['offer_value']);
                        if ($discountValue > $this->orderSummary['subtotal']) {
                            $discountValue = $this->orderSummary['subtotal'];
                        }
                    }
                }
            }
        }

        $this->orderSummary['discount'] = $discountValue;
        $this->orderSummary['grand_total'] = max(0, $this->orderSummary['subtotal'] - $this->orderSummary['discount']);
    }

    private function loadMenuData(?string $menuName = null)
    {
        try {

            return $this->menuService
                ->searchByName($menuName)
                ->get()
                ->toArray();

        } catch (\Exception $e) {

            return [];
        }
    }

    public function checkoutOrder()
    {
        $this->checkoutOrderModalState = true;
    }

    // FUNGSI PROCEED ORDER YANG SUDAH MEMILIKI LOGIKA OPEN BILL & QRIS
    public function proceedOrder()
    { 
        try { 
            // 1. Validasi Standar
            if (empty($this->cartItems)) return $this->toaster->error('Cart is empty'); 
            if (!isset($this->checkoutOrderData['customer_name'])) return $this->toaster->error('Customer name is required'); 
            if (!isset($this->checkoutOrderData['payment_method'])) return $this->toaster->error('Payment Method is required'); 
            if (!isset($this->checkoutOrderData['is_open_bill'])) return $this->toaster->error('Open Bill is required');

            // 2. CEK APAKAH INI OPEN BILL
            if ($this->checkoutOrderData['is_open_bill'] === 'Yes') {
                $this->createOrder('Pending');
                return; // Berhenti di sini jika Open Bill
            } 

            // 3. JIKA LANGSUNG BAYAR (Tutup form modal dulu)
            $this->checkoutOrderModalState = false;
            $method = $this->checkoutOrderData['payment_method'];

            // ==========================================
            // LOGIKA PEMBAYARAN (GABUNGAN VOUCHER & QRIS)
            // ==========================================
            if ($method === 'Bank Transfer' || $method === 'QRIS') {
                
                // Bank Transfer -> Tagihan utuh, keluar QRIS
                $this->paymentRemainder = $this->orderSummary['grand_total'];
                $this->qrisModalState = true; 

            } elseif ($method === 'Marketting Voucher') {
                
                // Menghitung jumlah potongan voucher (1 voucher = 50.000)
                $voucherAmount = intval($this->checkoutOrderData['voucher_quantity'] ?? 0) * 50000;
                
                // Kita gunakan grand_total agar jika ada diskon, diskonnya tetap dihitung!
                $totalTagihan = $this->orderSummary['grand_total']; 

                // CEK SISA BAYAR (Seperti kode teman Anda)
                if ($totalTagihan > $voucherAmount) {
                    // Jika ada sisa bayar -> Keluar QRIS dengan nominal sisa
                    $this->paymentRemainder = $totalTagihan - $voucherAmount;
                    $this->qrisModalState = true;
                } else {
                    // Jika voucher menutupi semua tagihan -> Langsung masuk order list
                    $this->createOrder('Done');
                }

            } else {
                
                // Metode Lain (Staff Meal, QC, Entertainment, Cash) -> Langsung masuk order list
                $this->createOrder('Done'); 
                
            }

        } catch (\Exception $e) {
            $this->handleError($e);
        } 
    }

    /**
     * Memunculkan popup dialog konfirmasi pembayaran.
     * Dipanggil saat kasir submit form Checkout.
     */
    public function requestConfirmation()
    {
        // Tampilkan dialog konfirmasi TallStackUI
        $this->dialog()
            ->success('Apakah Anda yakin?', 'Apakah Anda ingin melanjutkan pembayaran?')
            ->confirm('Ya, lanjutkan', 'proceedOrder') // Memanggil fungsi checkout jika klik 'Ya'
            ->cancel('Tidak, batalkan') // Menutup dialog jika klik 'Tidak'
            ->send();
    }

    // Menerima parameter status (Pending / Done)
    public function createOrder($status = 'Pending')
    { 
        try { 

            $finalSubtotal = $this->orderSummary['subtotal']; 
            $finalGrandTotal = $this->orderSummary['grand_total']; 
            $finalDiscountValue = 0; 
            $finalDiscountName = null; 

            // KODE BARU UNTUK FORMAT NAMA DISKON DI INVOICE
            if (!is_null($this->selectedDiscountId)) {   
                $discount = collect($this->discounts)->firstWhere('id', $this->selectedDiscountId); 
                
                $minPurchase = floatval($discount['minimum_purchase'] ?? 0);
                $minText = $minPurchase > 0 ? ' - Min. Rp ' . number_format($minPurchase, 0, ',', '.') : '';

                if (($discount['type'] ?? 'percentage') === 'percentage') {
                    $finalDiscountName = $discount['name'] . ' (' . $discount['offer_value'] . '%' . $minText . ')'; 
                } else {
                    $finalDiscountName = $discount['name'] . ' (Rp ' . number_format($discount['offer_value'], 0, ',', '.') . $minText . ')'; 
                }

                $finalDiscountValue = $this->orderSummary['discount']; 
            } 

            if ($this->checkoutOrderData['payment_method'] === 'Marketting Voucher') { 

                $voucherAmount
                    = intval($this->checkoutOrderData['voucher_quantity'] ?? 0)
                    * 50000; 

                $finalDiscountValue += $voucherAmount; 

                $finalDiscountName
                    = ($finalDiscountName ? $finalDiscountName.' + ' : '')
                    .'Voucher ('.$this->checkoutOrderData['voucher_quantity'].'x)'; 

                $finalGrandTotal
                    = max(0, $finalSubtotal - $finalDiscountValue); 
            } 

            if (in_array(
                $this->checkoutOrderData['payment_method'],
                ['Staff Meal', 'Entertainment', 'QC']
            )) { 

                $finalSubtotal = 0; 
                $finalGrandTotal = 0; 
            } 

            $orderData = [ 
                ...$this->checkoutOrderData, 
                'discount_name' => $finalDiscountName, 
                'discount_value' => $finalDiscountValue, 
                'cashier_name' => auth()->user()->fullname, 
                'subtotal' => $finalSubtotal, 
                'grand_total' => $finalGrandTotal, 
                'status' => $status 
            ]; 

            // Hapus field is_open_bill karena tidak ada di tabel database
            unset($orderData['is_open_bill']);

            // PERUBAHAN: CEK APAKAH INI UPDATE ATAU PESANAN BARU
            if ($this->existingOrderId) {
                $this->orderManagementService->updateOrder($this->existingOrderId, $orderData, $this->cartItems); 
                $this->toaster->success('Pesanan tambahan berhasil disimpan'); 
            } else {
                $this->orderManagementService->createOrder($orderData, $this->cartItems); 
                $this->toaster->success('Order successfully created'); 
            }

            $this->qrisModalState = false;
            $this->checkoutOrderModalState = false;

            $this->reset([
                'checkoutOrderData',
                'cartItems',
                'selectedDiscountId',
                'orderSummary',
                'existingOrderId' // Membersihkan URL dari parameter ID
            ]);

        } catch (\Exception $e) {
            $this->handleError($e);
        } 
    }

    public function confirmQrisPayment()
    {
        try {
            // Langsung buat order DONE setelah konfirmasi QRIS awal
            $this->createOrder('Done');
        } catch (\Exception $e) {
            $this->handleError($e);
        }
    }


    private function handleError(\Exception $e)
    {
        return $this->toaster->error($e->getMessage());
    }

    #[Layout('components.layouts.cashier')]
    public function render()
    {
        return view('livewire.cashier.create-order');
    }

    /**
     * Memunculkan popup konfirmasi khusus untuk tombol Update Pesanan (Open Bill)
     */
    public function requestUpdateConfirmation()
    {
        // Validasi keranjang kosong sebelum memunculkan pop-up
        if (empty($this->cartItems)) {
            return $this->toaster->error('Keranjang tidak boleh kosong!');
        }

        // Tampilkan dialog konfirmasi
        $this->dialog()
            ->question('Update Pesanan?', 'Apakah Anda yakin ingin menambahkan pesanan ini ke tagihan?')
            ->confirm('Ya, Update', 'processUpdateOrder') // Jika "Ya", jalankan fungsi proses update
            ->cancel('Batal')
            ->send();
    }

    /**
     * Fungsi untuk langsung memperbarui pesanan Open Bill
     * tanpa melewati form Checkout lagi.
     */
    public function processUpdateOrder()
    {
        // 1. Pastikan keranjang tidak kosong (Gunakan $this->cartItems)
        if (empty($this->cartItems)) {
            return $this->toaster->error('Keranjang tidak boleh kosong!');
        }

        try {
            // 2. Langsung panggil fungsi createOrder Anda! 
            // Karena fungsi createOrder() sudah bisa mendeteksi existingOrderId 
            // dan akan otomatis menjalankan updateOrder di dalamnya.
            $this->createOrder('Pending');

            // 3. Kembali ke halaman riwayat pesanan (Order List)
            // Catatan: Pastikan nama route 'cashier.order-list' sesuai dengan yang ada di file routes/web.php Anda
            return $this->redirect(route('cashier.order-list', ['tab' => 'open-bill']), navigate: true);

        } catch (\Exception $e) {
            $this->handleError($e);
        }
    }
}