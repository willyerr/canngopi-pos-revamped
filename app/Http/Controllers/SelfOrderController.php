<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

use App\Services\SelfOrderService;

class SelfOrderController extends Controller
{
    protected $selfOrderService;

    public function __construct(SelfOrderService $selfOrderService)
    {
        $this->selfOrderService = $selfOrderService;
    }

    public function index()
    {
        return 'GET draft order';
    }

    public function store(Request $request)
    {
        try
        {
            $order = $this->selfOrderService->create($request->all());
            return response()->json(['success' => true, 'data' => $order], 200);
        }
        catch(ValidationException $e)
        {
            return response()->json(['success' => false, 'message' =>'Payload validation failed', 'errors' => $e->errors()], 400);
        }
        catch(Exception $e)
        {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500); 
        }
    }
}
