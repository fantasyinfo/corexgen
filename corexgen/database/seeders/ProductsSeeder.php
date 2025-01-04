<?php
namespace Database\Seeders;

use App\Models\CategoryGroupTag;
use App\Models\ProductsServices;
use Illuminate\Database\Seeder;

class ProductsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $productsArray = [];
        $userId = 112;
        $companyId = 10;

        // Fetch category and tax IDs
        $categoryIds = CategoryGroupTag::where('type', CATEGORY_GROUP_TAGS_TYPES['KEY']['products_categories'])
            ->where('relation_type', CATEGORY_GROUP_TAGS_RELATIONS['KEY']['products_services'])
            ->where('status', 'active')
            ->where('company_id', $companyId)
            ->pluck('id')
            ->toArray();

        $taxIds = CategoryGroupTag::where('type', CATEGORY_GROUP_TAGS_TYPES['KEY']['products_taxs'])
            ->where('relation_type', CATEGORY_GROUP_TAGS_RELATIONS['KEY']['products_services'])
            ->where('status', 'active')
            ->where('company_id', $companyId)
            ->pluck('id')
            ->toArray();

        // Validate arrays
        if (empty($categoryIds) || empty($taxIds)) {
            throw new \Exception("Category IDs or Tax IDs are empty. Please ensure the database has valid data.");
        }

        // Generate product data
        for ($i = 0; $i <= 100; $i++) {
            $productsArray[] = [
                'type' => fake()->randomElement(['Product', 'Service']),
                'title' => fake()->words(3, true),
                'slug' => fake()->slug(),
                'description' => fake()->text(),
                'rate' => fake()->numberBetween(1111, 9999),
                'unit' => fake()->numberBetween(1, 10),
                'created_by' => $userId,
                'updated_by' => $userId,
                'company_id' => $companyId,
                'cgt_id' => $categoryIds[array_rand($categoryIds)],
                'tax_id' => $taxIds[array_rand($taxIds)],
            ];
        }

        // Chunked insert
        foreach (array_chunk($productsArray, 10) as $chunk) {
            ProductsServices::insert($chunk);
        }
    }
}
