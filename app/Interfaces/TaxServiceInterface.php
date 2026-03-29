<?php

namespace App\Interfaces;

use App\Models\Tax;

interface TaxServiceInterface
{
    public function getAllTaxes();
    public function getTaxById(int|string $id): ?Tax;
    public function createTax(array $data): Tax;
    public function updateTax(Tax $tax, array $data): Tax;
    public function deleteTax(Tax $tax): bool;
    public function getActiveTaxes();
}
