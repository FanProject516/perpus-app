<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
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
                'name' => 'Fiksi',
                'description' => 'Kategori untuk buku-buku fiksi',
                'children' => [
                    ['name' => 'Novel', 'description' => 'Novel dan karya sastra'],
                    ['name' => 'Cerita Pendek', 'description' => 'Kumpulan cerita pendek'],
                    ['name' => 'Fantasy', 'description' => 'Novel dan cerita fantasi'],
                    ['name' => 'Science Fiction', 'description' => 'Cerita fiksi ilmiah'],
                ]
            ],
            [
                'name' => 'Non-Fiksi',
                'description' => 'Kategori untuk buku-buku non-fiksi',
                'children' => [
                    ['name' => 'Biografi', 'description' => 'Buku biografi dan autobiografi'],
                    ['name' => 'Sejarah', 'description' => 'Buku sejarah dan dokumenter'],
                    ['name' => 'Self-Help', 'description' => 'Buku pengembangan diri'],
                ]
            ],
            [
                'name' => 'Pendidikan',
                'description' => 'Kategori untuk buku-buku pendidikan',
                'children' => [
                    ['name' => 'Matematika', 'description' => 'Buku matematika dan statistik'],
                    ['name' => 'Sains', 'description' => 'Buku sains dan teknologi'],
                    ['name' => 'Bahasa', 'description' => 'Buku pembelajaran bahasa'],
                    ['name' => 'Komputer', 'description' => 'Buku teknologi dan pemrograman'],
                ]
            ],
            [
                'name' => 'Agama',
                'description' => 'Kategori untuk buku-buku keagamaan',
                'children' => [
                    ['name' => 'Islam', 'description' => 'Buku keagamaan Islam'],
                    ['name' => 'Kristen', 'description' => 'Buku keagamaan Kristen'],
                    ['name' => 'Umum', 'description' => 'Buku keagamaan umum'],
                ]
            ],
            [
                'name' => 'Anak-anak',
                'description' => 'Kategori untuk buku anak-anak',
                'children' => [
                    ['name' => 'Dongeng', 'description' => 'Buku dongeng dan cerita anak'],
                    ['name' => 'Pendidikan Anak', 'description' => 'Buku edukasi untuk anak'],
                    ['name' => 'Komik Anak', 'description' => 'Komik untuk anak-anak'],
                ]
            ]
        ];

        foreach ($categories as $categoryData) {
            $parent = Category::create([
                'name' => $categoryData['name'],
                'description' => $categoryData['description'],
                'is_active' => true
            ]);

            if (isset($categoryData['children'])) {
                foreach ($categoryData['children'] as $childData) {
                    Category::create([
                        'name' => $childData['name'],
                        'description' => $childData['description'],
                        'parent_id' => $parent->id,
                        'is_active' => true
                    ]);
                }
            }
        }
    }
}
