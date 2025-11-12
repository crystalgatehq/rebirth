<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class SettingsTableSeeder extends Seeder
{
    public function run(): void
    {
        if (!\Schema::hasTable('settings')) {
            return;
        }

        $now = now();

        $settings = [
            [
                'uuid' => (string) Str::uuid(),
                'name' => 'APP_NAME',
                'slug' => 'app-name',
                'description' => 'Application display name',
                'default_value' => 'Rebirth',
                'current_value' => 'Rebirth',
                'data_type' => 'string',
                'group' => 'app',
                'sort_order' => 1,
                'is_public' => true,
                'options' => null,
                'status' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'uuid' => (string) Str::uuid(),
                'name' => 'SUPPORT_EMAIL',
                'slug' => 'support-email',
                'description' => 'Primary support email address',
                'default_value' => 'support@example.com',
                'current_value' => 'support@example.com',
                'data_type' => 'string',
                'group' => 'app',
                'sort_order' => 2,
                'is_public' => false,
                'options' => null,
                'status' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'uuid' => (string) Str::uuid(),
                'name' => 'DEFAULT_COUNTRY',
                'slug' => 'default-country',
                'description' => 'Default country code (ISO Alpha-2)',
                'default_value' => 'KE',
                'current_value' => 'KE',
                'data_type' => 'string',
                'group' => 'localization',
                'sort_order' => 3,
                'is_public' => true,
                'options' => null,
                'status' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'uuid' => (string) Str::uuid(),
                'name' => 'DATE_FORMAT',
                'slug' => 'date-format',
                'description' => 'Default date display format',
                'default_value' => 'Y-m-d',
                'current_value' => 'Y-m-d',
                'data_type' => 'string',
                'group' => 'localization',
                'sort_order' => 4,
                'is_public' => true,
                'options' => null,
                'status' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ];

        DB::table('settings')->upsert(
            $settings,
            ['name'],
            [
                'slug',
                'description',
                'default_value',
                'current_value',
                'data_type',
                'group',
                'sort_order',
                'is_public',
                'options',
                'status',
                'updated_at',
            ]
        );
    }
}
