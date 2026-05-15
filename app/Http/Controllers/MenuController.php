<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\MenuService;

class MenuController extends Controller
{
    protected $menuService;

    public function __construct(MenuService $menuService)
    {
        $this->menuService = $menuService;
    }

    public function index()
    {
        $menus = $this->menuService->list()->get();
        return response()->json([
            'status' => 200,
            'data' => $menus
        ]);
    }

    public function show($id)
    {
        try 
        {
            $menu = $this->menuService->show($id);
            return response()->json([
                'status' => 200,
                'data' => $menu
            ]);
        } 
        catch (\Exception $e) 
        {
            return response()->json([
                'status' => 404,
                'message' => 'Menu not found'
            ], 404);
        }
    }
}
