<?php

namespace App\Services;

use App\Interfaces\TaxServiceInterface;
use App\Models\Tax;

class TaxService implements TaxServiceInterface
{
    public function __construct(private readonly Tax $tax) {}

    public function getAllTaxes()
    {
        return $this->tax->orderBy('created_at', 'asc')->paginate(request()->per_page ?? 10);
    }

    public function getTaxById(int|string $id): ?Tax
    {
        return $this->tax->find($id);
    }

    public function createTax(array $data): Tax
    {
        return $this->tax->create($data);
    }

    public function updateTax(Tax $tax, array $data): Tax
    {
        $tax->update($data);
        return $tax;
    }

    public function deleteTax(Tax $tax): bool
    {
        // Podríamos validar si se está usando en algún producto antes de borrar
        return $tax->delete();
    }

    public function getActiveTaxes()
    {
        return $this->tax->where('is_active', true)->get();
    }
}
