<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreProductCategoryRequest;
use App\Http\Requests\UpdateProductCategoryRequest;
use App\Interfaces\ProductCategoryServiceInterface;
use App\Models\ProductCategory;
use App\Traits\Authorizes;

class ProductCategoryController extends Controller
{
    use Authorizes;
    protected readonly ProductCategoryServiceInterface $productCategoryService;


    public function __construct(ProductCategoryServiceInterface $productCategoryService)
    {
        $this->productCategoryService = $productCategoryService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $this->authorize('viewAny', ProductCategory::class);

        $productCategories = $this->productCategoryService->getAllProductCategories();
        return response([
            "status" => 'success',
            'message' => 'Product categories retrieved successfully',
            'data' => $productCategories
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreProductCategoryRequest $request)
    {
        $this->authorize('create', ProductCategory::class);

        $productCategory = $this->productCategoryService->createProductCategory($request->validated());

        if (!$productCategory) {
            return response([
                "status" => 'error',
                'message' => 'Error creating product category'
            ], 500);
        }

        return response([
            "status" => 'success',
            'message' => 'Product category created successfully',
            'data' => $productCategory
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(int|string $productCategoryId)
    {
        $productCategory = $this->productCategoryService->getProductCategoryById($productCategoryId);
        $this->authorize('view', $productCategory);

        if (!$productCategory) {
            return response([
                "status" => 'error',
                'message' => 'Product category not found'
            ], 404);
        }

        return response([
            "status" => 'success',
            'message' => 'Product category retrieved successfully',
            'data' => $productCategory
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateProductCategoryRequest $request, int|string $productCategoryId)
    {

        $productCategory = $this->productCategoryService->getProductCategoryById($productCategoryId);
        $this->authorize('update', $productCategory);

        if (!$productCategory) {
            return response([
                "status" => 'error',
                'message' => 'Product category not found'
            ], 404);
        }

        $productCategoryUpdated = $this->productCategoryService->updateProductCategory($productCategory, $request->validated());

        if (!$productCategoryUpdated) {
            return response([
                "status" => 'error',
                'message' => 'Error updating supplier'
            ], 500);
        }

        return response([
            "status" => 'success',
            'message' => 'Product category updated successfully'
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(int|string $productCategoryId)
    {
        $productCategory = $this->productCategoryService->getProductCategoryById($productCategoryId);

        $this->authorize('delete', $productCategory);

        if (!$productCategory) {
            return response([
                "status" => 'error',
                'message' => 'Product category not found'
            ], 404);
        }

        if (!$this->productCategoryService->deleteProductCategory($productCategory)) {
            return response([
                "status" => 'error',
                'message' => 'Error deleting supplier'
            ], 500);
        }

        return response([
            "status" => 'success',
            'message' => 'Product category deleted successfully'
        ]);
    }
}
