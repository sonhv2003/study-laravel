<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ArticlesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $articles = [
            [
                'title' => 'Article 1',
                'author' => 'Author 1',
                'category' => 'Category 1',
                'description' => 'Content of article 1',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'title' => 'Article 2',
                'author' => 'Author 2',
                'category' => 'Category 2',
                'description' => 'Content of article 2',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'title' => 'Article 3',
                'author' => 'Author 3',
                'category' => 'Category 3',
                'description' => 'Content of article 3',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];
        DB::table('article')->insert($articles);
    }
}
