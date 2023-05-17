<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ImagesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $images = [
            [
                'link' => '/storage/images/upload/oi.jpg',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'link' => '/storage/images/upload/dao.jpg',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'link' => '/storage/images/upload/pepsi.webp',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];
        DB::table('image')->insert($images);
    }
}
