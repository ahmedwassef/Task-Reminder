<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            [
                'name_ar' => 'التراخيص',
                'name_en' => 'Licenses',
                'color' => '#3B82F6',
                'icon' => 'document',
            ],
            [
                'name_ar' => 'العقود',
                'name_en' => 'Contracts',
                'color' => '#10B981',
                'icon' => 'contract',
            ],
            [
                'name_ar' => 'المهام',
                'name_en' => 'Tasks',
                'color' => '#F59E0B',
                'icon' => 'task',
            ],
            [
                'name_ar' => 'الوثائق',
                'name_en' => 'Documents',
                'color' => '#8B5CF6',
                'icon' => 'document',
            ],
            [
                'name_ar' => 'الاجتماعات',
                'name_en' => 'Meetings',
                'color' => '#EC4899',
                'icon' => 'meeting',
            ],
            [
                'name_ar' => 'المدفوعات',
                'name_en' => 'Payments',
                'color' => '#14B8A6',
                'icon' => 'payment',
            ],
            [
                'name_ar' => 'أخرى',
                'name_en' => 'Other',
                'color' => '#6B7280',
                'icon' => 'other',
            ],
        ];

        foreach ($categories as $category) {
            Category::create([
                ...$category,
                'user_id' => null,
                'is_system' => true,
            ]);
        }
    }
}
