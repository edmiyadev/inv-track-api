<?php

namespace App\Services;

use App\Interfaces\ProductCategoryServiceInterface;
use App\Models\ProductCategory;

class ProductCategoryService implements ProductCategoryServiceInterface
{
    public function getAllProductCategories()
    {
        return ProductCategory::orderBy('created_at', 'desc')->paginate(request()->per_page ?? 10);
    }

    public function getProductCategoryById(int|string $id)
    {
        return ProductCategory::find($id);
    }

    public function createProductCategory(array $data)
    {
        return ProductCategory::create($data);
    }

    public function updateProductCategory(ProductCategory $productCategory, array $data)
    {
        if (! $data) {
            return false;
        }

        return $productCategory->update($data);
    }

    public function deleteProductCategory(ProductCategory $productCategory)
    {
        return $productCategory->delete();
    }
}
