<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use App\Models\CommunicationCategory;

class CommunicationCategoriesTableSeeder extends Seeder
{
    public function run(): void
    {
        if (!Schema::hasTable('communication_categories') || !Schema::hasTable('communication_types')) {
            return;
        }

        $now = now();

        // Load communication types keyed by slug => id
        $types = DB::table('communication_types')->select('id', 'slug')->get();
        if ($types->isEmpty()) {
            return;
        }
        $typeIdBySlug = $types->pluck('id', 'slug');

        // Base categories to seed for each communication type
        $baseCategories = [
            ['Marketing', 'tag'],
            ['Transactional', 'receipt'],
            ['Alerts', 'alert-triangle'],
            ['Notifications', 'bell'],
            ['Service', 'settings'],
        ];

        $rows = [];
        foreach ($typeIdBySlug as $typeSlug => $typeId) {
            foreach ($baseCategories as [$name, $icon]) {
                $slug = Str::slug($typeSlug . '-' . $name);
                $rows[] = [
                    'uuid' => (string) Str::uuid(),
                    'communication_type_id' => $typeId,
                    'name' => $name,
                    'slug' => $slug,
                    'icon' => $icon,
                    'color' => match ($typeSlug) {
                        'sms' => '#3b82f6',
                        'email' => '#10b981',
                        'voice' => '#f59e0b',
                        'push' => '#ef4444',
                        'whatsapp' => '#25D366',
                        default => '#6b7280',
                    },
                    'description' => null,
                    'template' => null,
                    'parent_id' => null,
                    'lft' => null,
                    'rgt' => null,
                    'depth' => null,
                    'metadata' => null,
                    'configuration' => null,
                    'status' => CommunicationCategory::STATUS_ACTIVE,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }
        }

        if (!empty($rows)) {
            DB::table('communication_categories')->upsert(
                $rows,
                ['slug'],
                [
                    'communication_type_id',
                    'name',
                    'icon',
                    'color',
                    'description',
                    'template',
                    'parent_id',
                    'lft',
                    'rgt',
                    'depth',
                    'metadata',
                    'configuration',
                    'status',
                    'updated_at',
                ]
            );
        }
    }
}
