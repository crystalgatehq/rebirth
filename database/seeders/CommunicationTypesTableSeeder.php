<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use App\Models\CommunicationType;

class CommunicationTypesTableSeeder extends Seeder
{
    public function run(): void
    {
        if (!Schema::hasTable('communication_types')) {
            return;
        }

        $now = now();

        $types = [
            [
                'name' => 'SMS',
                'slug' => 'sms',
                'icon' => 'message-square',
                'color' => '#3b82f6',
                'description' => 'Short Message Service',
                'configuration' => null,
            ],
            [
                'name' => 'Email',
                'slug' => 'email',
                'icon' => 'mail',
                'color' => '#10b981',
                'description' => 'Electronic Mail',
                'configuration' => null,
            ],
            [
                'name' => 'Voice',
                'slug' => 'voice',
                'icon' => 'phone',
                'color' => '#f59e0b',
                'description' => 'Voice calls and IVR',
                'configuration' => null,
            ],
            [
                'name' => 'Push',
                'slug' => 'push',
                'icon' => 'bell',
                'color' => '#ef4444',
                'description' => 'Push notifications',
                'configuration' => null,
            ],
            [
                'name' => 'WhatsApp',
                'slug' => 'whatsapp',
                'icon' => 'message-circle',
                'color' => '#25D366',
                'description' => 'WhatsApp Business messaging',
                'configuration' => null,
            ],
        ];

        $rows = array_map(function ($t) use ($now) {
            return [
                'uuid' => (string) Str::uuid(),
                'name' => $t['name'],
                'slug' => $t['slug'],
                'icon' => $t['icon'],
                'color' => $t['color'],
                'description' => $t['description'],
                'configuration' => $t['configuration'],
                'status' => CommunicationType::STATUS_ACTIVE,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }, $types);

        DB::table('communication_types')->upsert(
            $rows,
            ['slug'],
            ['name', 'icon', 'color', 'description', 'configuration', 'status', 'updated_at']
        );
    }
}
