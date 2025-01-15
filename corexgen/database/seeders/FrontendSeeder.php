<?php

namespace Database\Seeders;

use App\Models\LandingPage;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class FrontendSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //
        foreach (FRONT_END_DEFAULT_SETTINGS as $section => $content) {
            $this->seedSection($section, $content);
        }
    }

    /**
     * Seed a section of the landing page
     */
    private function seedSection(string $section, array $content): void
    {
        // Create or update the section
        LandingPage::updateOrCreate(
            [
                'key' => strtolower($section),
                'type' => 'section'
            ],
            [
                'value' => $content
            ]
        );
    }
}
