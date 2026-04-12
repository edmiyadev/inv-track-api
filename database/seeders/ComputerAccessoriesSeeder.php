<?php

namespace Database\Seeders;

use App\Models\Customer;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\Supplier;
use App\Models\Tax;
use App\Models\Warehouse;
use Illuminate\Database\Seeder;

class ComputerAccessoriesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 0. Crear Impuestos
        $taxes = [
            ['name' => 'ITBIS 18%', 'rate' => 18.00],
            ['name' => 'ITBIS 16%', 'rate' => 16.00],
            ['name' => 'ITBIS 0%', 'rate' => 0.00],
        ];

        foreach ($taxes as $taxData) {
            Tax::firstOrCreate(['name' => $taxData['name']], [
                'percentage' => $taxData['rate'],
                'description' => 'Impuesto local ' . $taxData['name'],
            ]);
        }

        // 1. Crear Clientes Realistas
        $customers = [
            [
                'name' => 'Gamer Center RD',
                'email' => 'compras@gamercenter.com.do',
                'tax_id' => '130987123',
                'phone_number' => '8097775555',
            ],
            [
                'name' => 'Eduardo Tech - Content Creator',
                'email' => 'eduardo@techreview.com',
                'tax_id' => '00198765432',
                'phone_number' => '8294443333',
            ],
            [
                'name' => 'Corporación Innova Tech',
                'email' => 'it@innovatech.com',
                'tax_id' => '101665544',
                'phone_number' => '8092221111',
            ],
        ];

        foreach ($customers as $customerData) {
            Customer::firstOrCreate(['email' => $customerData['email']], $customerData);
        }

        // 2. Crear Categorías Realistas
        $categories = [
            'Periféricos' => ['Teclados', 'Mouse', 'Auriculares', 'Webcams'],
            'Monitores' => ['Gamer', 'Oficina', 'Curvos', '4K'],
            'Almacenamiento' => ['SSD Internos', 'Discos Externos', 'Memorias USB'],
            'Componentes' => ['Tarjetas de Video', 'Memorias RAM', 'Fuentes de Poder', 'Procesadores'],
            'Redes' => ['Routers', 'Adaptadores Wi-Fi', 'Switches'],
            'Accesorios' => ['Mousepads', 'Soportes de Monitor', 'Cables HDMI/DP'],
        ];

        $categoryModels = [];
        foreach ($categories as $mainCategory => $subCategories) {
            foreach ($subCategories as $sub) {
                $categoryModels[$sub] = ProductCategory::firstOrCreate(['name' => $sub]);
            }
        }

        // 3. Crear Proveedores Realistas
        $suppliers = [
            [
                'name' => 'TechDistributor S.R.L.',
                'rnc' => '130987654',
                'phone_number' => '8095551234',
                'email' => 'ventas@techdistributor.com',
                'address' => 'Av. Winston Churchill #123, Santo Domingo',
                'is_active' => true,
            ],
            [
                'name' => 'LogiSales Dominicanas',
                'rnc' => '131234567',
                'phone_number' => '8094449876',
                'email' => 'info@logisales.do',
                'address' => 'Plaza Central, Local 45, Santo Domingo',
                'is_active' => true,
            ],
            [
                'name' => 'Global Tech Imports',
                'rnc' => '101887766',
                'phone_number' => '8293334444',
                'email' => 'import@globaltech.com',
                'address' => 'Zona Franca Las Americas, Edif 4',
                'is_active' => true,
            ],
        ];

        foreach ($suppliers as $supplierData) {
            Supplier::firstOrCreate(['rnc' => $supplierData['rnc']], $supplierData);
        }

        // 4. Crear Almacenes Realistas
        $warehouses = [
            [
                'name' => 'Almacén Principal - Santo Domingo',
                'code' => 'WH-SDQ-01',
                'location' => 'Av. Luperon Esq. Enriquillo',
                'descripcion' => 'Almacén central de distribución mayorista',
                'is_active' => true,
            ],
            [
                'name' => 'Showroom - Piantini',
                'code' => 'WH-SRM-02',
                'location' => 'Plaza Mezzaluna, 2do Nivel',
                'descripcion' => 'Exhibición y ventas al detalle',
                'is_active' => true,
            ],
            [
                'name' => 'Almacén Regional - Santiago',
                'code' => 'WH-STI-03',
                'location' => 'Autopista Duarte Km 5',
                'descripcion' => 'Distribución zona norte',
                'is_active' => true,
            ],
        ];

        foreach ($warehouses as $warehouseData) {
            Warehouse::firstOrCreate(['code' => $warehouseData['code']], $warehouseData);
        }

        // 5. Crear Productos Realistas
        $products = [
            // Teclados
            ['name' => 'Logitech G Pro Mechanical Keyboard', 'sku' => 'LOG-K-GPRO', 'cat' => 'Teclados', 'price' => 129.99],
            ['name' => 'Razer BlackWidow V3', 'sku' => 'RAZ-K-BWV3', 'cat' => 'Teclados', 'price' => 139.99],
            ['name' => 'Corsair K70 RGB MK.2', 'sku' => 'COR-K-K70', 'cat' => 'Teclados', 'price' => 159.99],
            
            // Mouse
            ['name' => 'Logitech G502 Hero', 'sku' => 'LOG-M-G502', 'cat' => 'Mouse', 'price' => 49.99],
            ['name' => 'Razer DeathAdder V2', 'sku' => 'RAZ-M-DAV2', 'cat' => 'Mouse', 'price' => 69.99],
            ['name' => 'SteelSeries Rival 600', 'sku' => 'SS-M-R600', 'cat' => 'Mouse', 'price' => 79.99],

            // Monitores
            ['name' => 'ASUS ROG Swift 27" 144Hz', 'sku' => 'ASU-MN-ROG27', 'cat' => 'Gamer', 'price' => 499.99],
            ['name' => 'Samsung Odyssey G7 32"', 'sku' => 'SAM-MN-G7-32', 'cat' => 'Gamer', 'price' => 699.99],
            ['name' => 'Dell UltraSharp 24"', 'sku' => 'DEL-MN-U24', 'cat' => 'Oficina', 'price' => 249.99],

            // Almacenamiento
            ['name' => 'Samsung 980 Pro 1TB NVMe', 'sku' => 'SAM-SSD-980P1', 'cat' => 'SSD Internos', 'price' => 159.99],
            ['name' => 'Western Digital Blue 2TB SSD', 'sku' => 'WD-SSD-BL2T', 'cat' => 'SSD Internos', 'price' => 189.99],
            ['name' => 'Crucial MX500 500GB', 'sku' => 'CRU-SSD-MX500', 'cat' => 'SSD Internos', 'price' => 59.99],

            // Componentes
            ['name' => 'NVIDIA RTX 4070 Ti', 'sku' => 'NV-GPU-4070TI', 'cat' => 'Tarjetas de Video', 'price' => 799.99],
            ['name' => 'Corsair Vengeance RGB 32GB RAM', 'sku' => 'COR-RAM-32V', 'cat' => 'Memorias RAM', 'price' => 124.99],
            ['name' => 'EVGA 750W 80+ Gold PSU', 'sku' => 'EVG-PSU-750G', 'cat' => 'Fuentes de Poder', 'price' => 109.99],
        ];

        foreach ($products as $pData) {
            Product::firstOrCreate(
                ['sku' => $pData['sku']],
                [
                    'name' => $pData['name'],
                    'description' => 'Descripción para ' . $pData['name'] . ', un producto de alta calidad de la categoría ' . $pData['cat'],
                    'price' => $pData['price'],
                    'product_category_id' => $categoryModels[$pData['cat']]->id,
                ]
            );
        }
    }
}
