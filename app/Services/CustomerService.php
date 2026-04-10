<?php

namespace App\Services;

use App\Interfaces\CustomerServiceInterface;
use App\Models\Customer;

class CustomerService implements CustomerServiceInterface
{
    public function __construct(private readonly Customer $customer) {}

    public function createCustomer(array $data): Customer
    {
        return $this->customer->create($data);
    }

    public function updateCustomer(Customer $customer, array $data): Customer
    {
        $customer->update($data);
        return $customer;
    }

    public function getCustomerById(int|string $id): ?Customer
    {
        return $this->customer->find($id);
    }

    public function getAllCustomers()
    {
        return $this->customer->orderBy('created_at', 'desc')->paginate(request()->per_page ?? 10);
    }

    public function deleteCustomer(Customer $customer): bool
    {
        return $customer->delete();
    }
}
