<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreProductRequest;
use App\Http\Requests\UpdateProductRequest;
use App\Interfaces\ProductServiceInterface;
use App\Models\Product;
use App\Traits\Authorizes;

class ProductController extends Controller
{
    use Authorizes;

    protected readonly ProductServiceInterface $productService;

    public function __construct(ProductServiceInterface $productService)
    {
        $this->productService = $productService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $this->authorize('viewAny', Product::class);

        $products = $this->productService->getAllProducts();

        return response([
            'status' => 'success',
            'message' => 'Products retrieved successfully',
            'data' => $products,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreProductRequest $request)
    {
        $this->authorize('create', Product::class);

        $product = $this->productService->createProduct($request->validated());

        if (! $product) {
            return response([
                'status' => 'error',
                'message' => 'Error creating product',
            ], 500);
        }

        return response([
            'status' => 'success',
            'message' => 'Product created successfully',
            'data' => $product,
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(int|string $productId)
    {
        $product = $this->productService->getProductById($productId);
        $this->authorize('view', $product);

        if (! $product) {
            return response([
                'status' => 'error',
                'message' => 'Product not found',
            ], 404);
        }

        return response([
            'status' => 'success',
            'message' => 'Product retrieved successfully',
            'data' => $product,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateProductRequest $request, int|string $productId)
    {

        $product = $this->productService->getProductById($productId);
        $this->authorize('update', $product);

        if (! $product) {
            return response([
                'status' => 'error',
                'message' => 'Product not found',
            ], 404);
        }

        $productUpdated = $this->productService->updateProduct($product, $request->validated());

        if (! $productUpdated) {
            return response([
                'status' => 'error',
                'message' => 'Error updating supplier',
            ], 500);
        }

        return response([
            'status' => 'success',
            'message' => 'Product updated successfully',
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(int|string $productId)
    {
        $product = $this->productService->getProductById($productId);

        $this->authorize('delete', $product);

        if (! $product) {
            return response([
                'status' => 'error',
                'message' => 'Product not found',
            ], 404);
        }

        if (! $this->productService->deleteProduct($product)) {
            return response([
                'status' => 'error',
                'message' => 'Error deleting supplier',
            ], 500);
        }

        return response([
            'status' => 'success',
            'message' => 'Product deleted successfully',
        ]);
    }
}
