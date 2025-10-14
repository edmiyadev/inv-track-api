<?php

namespace App\Interfaces;

use App\Models\ProductCategory;

interface ProductCategoryServiceInterface
{
    public function getAllProductCategories();
    public function getProductCategoryById(int|string $id);
    public function createProductCategory(array $data);
    public function updateProductCategory(ProductCategory $productCategory, array $data);
    public function deleteProductCategory(ProductCategory $productCategory);
}
