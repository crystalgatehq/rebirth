<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class FactoriesTableSeeder extends Seeder
{
    public function run(): void
    {
        if (!Schema::hasTable('factories') || !Schema::hasTable('counties')) {
            return;
        }

        $now = now();

        $data = [
            [
                'factory_code' => 'FCT-NAI-001',
                'name' => 'Nairobi Central Factory',
                'county_code' => '047',
                'base_url' => 'https://nai-factory.example.com',
                'api_user' => 'api_nairobi',
                'api_user_credentials' => 'secret',
                'description' => null,
            ],
            [
                'factory_code' => 'FCT-MOM-001',
                'name' => 'Mombasa Coast Factory',
                'county_code' => '001',
                'base_url' => 'https://mom-factory.example.com',
                'api_user' => 'api_mombasa',
                'api_user_credentials' => 'secret',
                'description' => null,
            ],
            [
                'factory_code' => 'FCT-KSM-001',
                'name' => 'Kisumu Lake Factory',
                'county_code' => '042',
                'base_url' => 'https://ksm-factory.example.com',
                'api_user' => 'api_kisumu',
                'api_user_credentials' => 'secret',
                'description' => null,
            ],
        ];

        $rows = [];
        foreach ($data as $item) {
            $county = DB::table('counties')->where('code', $item['county_code'])->first();
            if (!$county) {
                continue;
            }
            $rows[] = [
                'county_id' => $county->id,
                'factory_code' => $item['factory_code'],
                'name' => $item['name'],
                'slug' => Str::slug($item['name']),
                'description' => $item['description'],
                'base_url' => $item['base_url'],
                'api_user' => $item['api_user'],
                'api_user_credentials' => $item['api_user_credentials'],
                'status' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        if (!empty($rows)) {
            DB::table('factories')->upsert(
                $rows,
                ['factory_code'],
                ['county_id', 'name', 'slug', 'description', 'base_url', 'api_user', 'api_user_credentials', 'status', 'updated_at']
            );
        }
    }
}
