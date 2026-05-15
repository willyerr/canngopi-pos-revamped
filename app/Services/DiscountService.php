<?php

namespace App\Services;

use Illuminate\Validation\ValidationException;
use App\Models\Discount;
use Illuminate\Support\Facades\Validator;

class DiscountService
{
    /**
     * Fungsi baru untuk membuat aturan validasi secara dinamis
     * berdasarkan tipe diskon yang dipilih.
     */
    private function getValidationRules(array $data): array
    {
        $rules = [
            'name' => ['required', 'string'],
            'description' => ['nullable', 'string'],
            'terms_and_condition' => ['nullable', 'string'],
            'type' => ['required', 'in:percentage,nominal'],
            'offer_value' => ['required', 'numeric', 'min:1'], // Hapus max:100 dari aturan default
            'minimum_purchase' => ['nullable', 'numeric', 'min:0'],
        ];

        // Jika tipenya adalah persentase, baru kita tambahkan batas maksimal 100
        if (($data['type'] ?? 'percentage') === 'percentage') {
            $rules['offer_value'][] = 'max:100';
        }

        return $rules;
    }

    public function list()
    {
        return Discount::query();
    }

    public function show(int $id): Discount
    {
        return Discount::findOrFail($id);
    }

    public function store(array $discount): Discount
    {
        // Gunakan aturan validasi dinamis
        $validator = Validator::make($discount, $this->getValidationRules($discount));
        
        if($validator->fails())
            throw new ValidationException($validator);

        return Discount::create($discount);
    }

    public function edit(int $id, array $discount): bool
    {
        // Gunakan aturan validasi dinamis
        $validator = Validator::make($discount, $this->getValidationRules($discount));
        
        if($validator->fails())
            throw new ValidationException($validator);

        return Discount::where('id', $id)->update($discount);
    }

    public function delete(int $id): bool
    {
        return Discount::destroy($id);
    }
}