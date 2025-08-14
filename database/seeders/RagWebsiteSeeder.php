<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\RagWebsite;

class RagWebsiteSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $websites = [
            [
                'name' => 'Laravel Documentation',
                'url' => 'https://laravel.com/docs',
                'description' => 'Official Laravel documentation and guides',
                'content' => 'Laravel is a web application framework with expressive, elegant syntax. It provides tools and features for building modern web applications including routing, authentication, sessions, caching, and more. Laravel follows the MVC architectural pattern and includes an ORM called Eloquent for database interactions.',
                'is_active' => true,
                'last_scraped_at' => now(),
                'metadata' => [
                    'language' => 'en',
                    'category' => 'documentation',
                    'framework' => 'laravel'
                ]
            ],
            [
                'name' => 'TypeScript Handbook',
                'url' => 'https://www.typescriptlang.org/docs/',
                'description' => 'Official TypeScript documentation and handbook',
                'content' => 'TypeScript is a strongly typed programming language that builds on JavaScript. It provides static type definitions and helps catch errors during development. TypeScript supports modern JavaScript features and compiles to plain JavaScript that runs anywhere.',
                'is_active' => true,
                'last_scraped_at' => now(),
                'metadata' => [
                    'language' => 'en',
                    'category' => 'documentation',
                    'framework' => 'typescript'
                ]
            ],
            [
                'name' => 'Example Company Website',
                'url' => 'https://example-company.com',
                'description' => 'Sample company website for demonstration',
                'content' => 'Welcome to Example Company. We provide innovative solutions for modern businesses. Our services include web development, mobile applications, and AI-powered tools. Contact us for consultation and custom development projects.',
                'is_active' => true,
                'last_scraped_at' => now(),
                'metadata' => [
                    'language' => 'en',
                    'category' => 'business',
                    'type' => 'company'
                ]
            ]
        ];

        foreach ($websites as $websiteData) {
            RagWebsite::updateOrCreate(
                ['url' => $websiteData['url']],
                $websiteData
            );
        }
    }
}
