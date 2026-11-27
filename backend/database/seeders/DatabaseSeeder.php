<?php

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $products = [
            ['Gà ri thả vườn', 'ga-ri-tha-vuon', 'Gà tươi', 'Thịt chắc, da vàng tự nhiên. Gà được thả vườn, sơ chế trong ngày.', 185000, 158000, 'kg', 'https://images.unsplash.com/photo-1604503468506-a8da13d82791?auto=format&fit=crop&w=900&q=85', 'BÁN CHẠY'],
            ['Gà đồi Siêu Môn', 'ga-doi-sieu-mon', 'Gà tươi', 'Lớn tự nhiên trên đồi, thịt thơm ngọt và săn chắc.', 210000, 179000, 'kg', 'https://images.unsplash.com/photo-1548550023-2bdb3c5beed7?auto=format&fit=crop&w=900&q=85', 'ĐẶC SẢN'],
            ['Trứng gà ta (10 quả)', 'trung-ga-ta', 'Trứng & thực phẩm', 'Trứng mới mỗi ngày, lòng đỏ đậm màu, giàu dinh dưỡng.', 45000, 38000, 'hộp', 'https://images.unsplash.com/photo-1518569656558-1f25e69d93d7?auto=format&fit=crop&w=900&q=85', 'TƯƠI MỖI NGÀY'],
            ['Gà ủ muối hoa tiêu', 'ga-u-muoi-hoa-tieu', 'Chế biến sẵn', 'Gà ta ủ muối thơm dịu, chế biến sẵn, tiện cho bữa cơm nhà.', 265000, 229000, 'con', 'https://images.unsplash.com/photo-1598103442097-8b74394b95c6?auto=format&fit=crop&w=900&q=85', 'SƠ CHẾ SẴN'],
            ['Đùi gà ri (500g)', 'dui-ga-ri', 'Gà tươi', 'Phần đùi chắc thịt, đóng gói hút chân không tiện bảo quản.', 98000, 84000, 'khay', 'https://images.unsplash.com/photo-1607623814075-e51df1bdc82f?auto=format&fit=crop&w=900&q=85', null],
            ['Cánh gà thả vườn (500g)', 'canh-ga-tha-vuon', 'Gà tươi', 'Cánh gà tươi ngon, phù hợp món chiên, nướng cho cả nhà.', 89000, 76000, 'khay', 'https://images.unsplash.com/photo-1626082927389-6cd097cdc6ec?auto=format&fit=crop&w=900&q=85', null],
            ['Chả gà nhà làm', 'cha-ga-nha-lam', 'Chế biến sẵn', 'Chả gà mềm thơm từ thịt gà tươi của trại, không chất bảo quản.', 135000, 115000, 'gói 500g', 'https://images.unsplash.com/photo-1604908176997-125f25cc6f3d?auto=format&fit=crop&w=900&q=85', 'NHÀ LÀM'],
            ['Combo bữa cơm nhà', 'combo-bua-com-nha', 'Combo tiết kiệm', 'Gà nguyên con, trứng gà ta và gia vị cho bữa cơm tròn vị.', 299000, 259000, 'combo', 'https://images.unsplash.com/photo-1547592180-85f173990554?auto=format&fit=crop&w=900&q=85', 'TIẾT KIỆM'],
        ];
        $segments = [
            'ga-ri-tha-vuon' => 'standard', 'ga-doi-sieu-mon' => 'premium',
            'trung-ga-ta' => 'economy', 'ga-u-muoi-hoa-tieu' => 'premium',
            'dui-ga-ri' => 'economy', 'canh-ga-tha-vuon' => 'economy',
            'cha-ga-nha-lam' => 'standard', 'combo-bua-com-nha' => 'standard',
        ];
        foreach ($products as [$name, $slug, $category, $description, $price, $wholesale, $unit, $image, $badge]) {
            $product = Product::updateOrCreate(['slug' => $slug], compact('name', 'slug', 'category', 'description', 'price', 'unit', 'image', 'badge') + ['wholesale_price' => $wholesale, 'is_active' => true]);
            if ($product->wasRecentlyCreated) {
                $product->update(['segment' => $segments[$slug]]);
            }
        }

        $this->call(DemoCatalogSeeder::class);
    }
}
