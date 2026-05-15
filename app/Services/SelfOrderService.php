<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

use App\Models\DraftOrder;
use App\Services\OrderManagementService;

class SelfOrderService
{
    public $fields = [
        'customer_name' => ['required', 'string'],
        'customer_email' => ['nullable', 'email'],
        'table_number' => ['required', 'numeric'],
        'order_type' => ['required', 'string', 'in:Dine In,Take Away'],
        'items' => ['required', 'array', 'min:1'],
        'items.*.menu_id' => ['required', 'integer', 'exists:menus,id'],
        'items.*.quantity' => ['required', 'integer', 'min:1'],
        'items.*.notes' => ['nullable', 'string']
    ];

    public function __construct(private OrderManagementService $orderManagementService) {}

    public function list()
    {
        return DraftOrder::with(['items.menu:id,name,category,price'])->get();
    }

    public function create(array $data)
    {
        $validator = Validator::make($data, $this->fields);
        if($validator->fails())
            throw new ValidationException($validator);

        DB::beginTransaction();
        try
        {
            $draftOrder = DraftOrder::create([
                'customer_name' => $data['customer_name'],
                'customer_email' => $data['customer_email'] ?? null,
                'table_number' => $data['table_number'],
                'order_type' => $data['order_type']
            ]);

            if(!empty($data['items']))
            {
                foreach($data['items'] as $item)
                {
                    $draftOrder->items()->create([
                        'item_id' => $item['menu_id'],
                        'quantity' => $item['quantity'],
                        'notes' => $item['notes'] ?? null
                    ]);
                }
            }

            DB::commit();
            return $draftOrder->load('items');
        }
        catch(Exception $e)
        {
            DB::rollBack();
            throw $e;
        }
    }

    public function convertDraftToPaidOrder(int $draftOrderId, array $finalOrderInfo, array $finalOrderItems)
    {
        DB::BeginTransaction();
        try
        {
            $order = $this->orderManagementService->createOrder($finalOrderInfo, $finalOrderItems);
            $this->remove($draftOrderId);
            DB::commit();
            return $order;
        }
        catch(Exception $e)
        {
            DB::rollBack();
            throw $e;
        }
    }

    public function remove(int $draftOrderId)
    {
        return DraftOrder::where('id', $draftOrderId)->delete();
    }

    public function removeByDate($date)
    {
        return DraftOrder::whereDate('created_at', $date)->delete();
    }
}
