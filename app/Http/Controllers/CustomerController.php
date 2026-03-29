<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCustomerRequest;
use App\Http\Requests\UpdateCustomerRequest;
use App\Interfaces\CustomerServiceInterface;
use App\Models\Customer;
use App\Traits\Authorizes;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    use Authorizes;

    public function __construct(
        private readonly CustomerServiceInterface $customerService
    ) {}

    public function index()
    {
        $this->authorize('viewAny', Customer::class);
        $customers = $this->customerService->getAllCustomers();

        return response([
            'status' => 'success',
            'message' => 'Customers retrieved successfully',
            'data' => $customers,
        ]);
    }

    public function store(StoreCustomerRequest $request)
    {
        $this->authorize('create', Customer::class);
        $customer = $this->customerService->createCustomer($request->validated());

        return response([
            'status' => 'success',
            'message' => 'Customer created successfully',
            'data' => $customer,
        ], 201);
    }

    public function show(int|string $id)
    {
        $customer = $this->customerService->getCustomerById($id);
        $this->authorize('view', $customer);

        if (! $customer) {
            return response([
                'status' => 'error',
                'message' => 'Customer not found',
            ], 404);
        }

        return response([
            'status' => 'success',
            'message' => 'Customer retrieved successfully',
            'data' => $customer,
        ]);
    }

    public function update(UpdateCustomerRequest $request, int|string $id)
    {
        $customer = $this->customerService->getCustomerById($id);
        $this->authorize('update', $customer);

        if (! $customer) {
            return response([
                'status' => 'error',
                'message' => 'Customer not found',
            ], 404);
        }

        $customerUpdated = $this->customerService->updateCustomer($customer, $request->validated());

        return response([
            'status' => 'success',
            'message' => 'Customer updated successfully',
            'data' => $customerUpdated,
        ]);
    }

    public function destroy(int|string $id)
    {
        $customer = $this->customerService->getCustomerById($id);
        $this->authorize('delete', $customer);

        if (! $customer) {
            return response([
                'status' => 'error',
                'message' => 'Customer not found',
            ], 404);
        }

        $this->customerService->deleteCustomer($customer);

        return response([
            'status' => 'success',
            'message' => 'Customer deleted successfully',
        ]);
    }
}
