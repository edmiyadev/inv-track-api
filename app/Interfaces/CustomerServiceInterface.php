<?php

namespace App\Interfaces;

use App\Models\Customer;

interface CustomerServiceInterface
{
    public function createCustomer(array $data): Customer;
    public function updateCustomer(Customer $customer, array $data): Customer;
    public function getCustomerById(int|string $id): ?Customer;
    public function getAllCustomers();
    public function deleteCustomer(Customer $customer): bool;
}
