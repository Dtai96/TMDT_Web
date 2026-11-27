<?php

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Seeder;

class DemoCatalogSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Example prices and descriptions must be confirmed by the farm.
        $chicken = 'https://images.unsplash.com/photo-1604503468506-a8da13d82791?auto=format&fit=crop&w=900&q=85';
        $eggs = 'https://images.unsplash.com/photo-1518569656558-1f25e69d93d7?auto=format&fit=crop&w=900&q=85';
        $prepared = 'https://images.unsplash.com/photo-1598103442097-8b74394b95c6?auto=format&fit=crop&w=900&q=85';
        $combo = 'https://images.unsplash.com/photo-1547592180-85f173990554?auto=format&fit=crop&w=900&q=85';
        $items = [
            ['Ức gà (500g)', 'demo-uc-ga-500g', 'Gà tươi', 'economy', 'Ức gà đóng khay 500g, phù hợp nhiều món ăn gia đình.', 75000, 'khay', $chicken],
            ['Gà ta nguyên con', 'demo-ga-ta-nguyen-con', 'Gà tươi', 'standard', 'Gà ta nguyên con cho bữa cơm gia đình.', 175000, 'kg', $chicken],
            ['Gà thả vườn nguyên con', 'demo-ga-tha-vuon-nguyen-con', 'Gà tươi', 'premium', 'Gà thả vườn nguyên con theo quy cách của trại.', 225000, 'kg', $chicken],
            ['Trứng gà (6 quả)', 'demo-trung-ga-6-qua', 'Trứng & thực phẩm', 'economy', 'Hộp 6 quả thuận tiện cho gia đình nhỏ.', 30000, 'hộp', $eggs],
            ['Trứng gà (10 quả)', 'demo-trung-ga-10-qua', 'Trứng & thực phẩm', 'standard', 'Hộp 10 quả cho bữa ăn hằng ngày.', 48000, 'hộp', $eggs],
            ['Trứng gà thả vườn (10 quả)', 'demo-trung-tha-vuon-10-qua', 'Trứng & thực phẩm', 'premium', 'Trứng gà thả vườn theo quy cách hộp 10 quả.', 65000, 'hộp', $eggs],
            ['Chả gà (250g)', 'demo-cha-ga-250g', 'Chế biến sẵn', 'economy', 'Gói chả gà 250g cho bữa ăn nhỏ.', 65000, 'gói', $prepared],
            ['Gà xé (500g)', 'demo-ga-xe-500g', 'Chế biến sẵn', 'standard', 'Gà xé đóng gói 500g, phù hợp nhiều món.', 135000, 'gói', $prepared],
            ['Gà ủ muối nguyên con', 'demo-ga-u-muoi-nguyen-con', 'Chế biến sẵn', 'premium', 'Gà ủ muối nguyên con theo quy cách của trại.', 285000, 'con', $prepared],
            ['Combo bữa ăn nhỏ', 'demo-combo-bua-an-nho', 'Combo tiết kiệm', 'economy', 'Combo gà theo phần và trứng dành cho bữa ăn nhỏ.', 125000, 'combo', $combo],
            ['Combo gia đình', 'demo-combo-gia-dinh', 'Combo tiết kiệm', 'standard', 'Combo gà và trứng cho bữa ăn gia đình.', 275000, 'combo', $combo],
            ['Combo gà thả vườn', 'demo-combo-tha-vuon', 'Combo tiết kiệm', 'premium', 'Combo gà thả vườn và trứng theo quy cách của trại.', 365000, 'combo', $combo],
        ];

        foreach ($items as [$name, $slug, $category, $segment, $description, $price, $unit, $image]) {
            Product::firstOrCreate(['slug' => $slug], [
                'name' => $name,
                'category' => $category,
                'segment' => $segment,
                'description' => $description,
                'price' => $price,
                'wholesale_price' => $price,
                'unit' => $unit,
                'image' => $image,
                'badge' => null,
                'is_active' => true,
            ]);
        }
    }
}
