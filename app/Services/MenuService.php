<?php

namespace App\Services;

use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

use App\Models\Menu;

class MenuService
{
    private $fields = [
        'name' => ['required', 'string', 'max:120'],
        'price' => ['required', 'integer', 'min:1000'],
        'category' => ['required', 'string', 'in:Signature,Breakfast,Dessert,Snack,Pizza,Burger & Sandwich,Soup,Pasta,Coffee,Non Coffee,Mocktail,Donbury,Others'],
        'image' => ['nullable', 'image', 'mimes:png,jpg']
    ];

    public function list()
    {
        return Menu::query();
    }

    public function searchByName(?string $menuName = null)
    {
        return Menu::where('name', 'like', '%' . $menuName . '%');
    }

    public function show(int $id): Menu
    {
        return Menu::findOrFail($id);
    }

    public function store(array $menu): Menu
    {
        $validator = Validator::make($menu, $this->fields);
        if($validator->fails()) 
            throw new ValidationException($validator);
        
        if(isset($menu['image']))
        {
            $fileName = time() . '.' . $menu['image']->getClientOriginalExtension(); 
            $menu['image'] = $menu['image']->storeAs('menu-images', $fileName, 'public');
        }

        return Menu::create($menu);
    }

    public function edit(int $id, array $menu): bool
    {
        $validator = Validator::make($menu, $this->fields);
        if($validator->fails()) 
            throw new ValidationException($validator);
        
        $oldMenu = Menu::findOrFail($id);

        if(array_key_exists('image', $menu)) 
        {
            if(!is_null($menu['image'])) 
            {
                $oldImagePath = storage_path('app/public/' . $oldMenu->image);
    
                $fileName = time() . '.' . $menu['image']->getClientOriginalExtension(); 
                $menu['image'] = $menu['image']->storeAs('menu-images', $fileName, 'public');
    
                if ($oldMenu->image && file_exists($oldImagePath))
                    unlink($oldImagePath);
            } 
            else unset($menu['image']);
        }
        
        return $oldMenu->update($menu);
    }

    public function delete(int $id): bool
    {
        $menu = $this->show($id);
        if(!is_null($menu->image))
            unlink(storage_path('app/public/' . $menu->image));

        return $menu->delete();
    }
}
