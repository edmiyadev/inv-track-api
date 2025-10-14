<?php

namespace App\Services;

use App\Interfaces\ProductServiceInterface;
use App\Models\Product;

class ProductService implements ProductServiceInterface
{
    public function getAllProducts()
    {
        return Product::all();
    }
    public function getProductById(int|string $id)
    {
        return Product::find($id);
    }
    public function createProduct(array $data)
    {
        return Product::create($data);
    }
    public function updateProduct(Product $product, array $data)
    {
        if (!$data) {
            return false;
        }

        return $product->update($data);
    }
    public function deleteProduct(Product $product)
    {
        return $product->delete();
    }
}
