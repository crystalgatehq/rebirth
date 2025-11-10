<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class AfricasTalkingSettingsSeeder extends Seeder
{
    public function run(): void
    {
        $settings = [
            [
                'name' => 'AFRICASTALKING_USERNAME',
                '_slug' => 'africastalking-username',
                'description' => 'Africa\'s Talking API username',
                'default_value' => '',
                'current_value' => env('AFRICASTALKING_USERNAME', ''),
                'data_type' => 'string',
                'group' => 'africas_talking',
                'is_public' => false,
                'options' => [
                    'type' => 'text',
                    'validation' => ['required', 'string'],
                    'ui' => [
                        'component' => 'text-input',
                        'placeholder' => 'Enter your Africa\'s Talking username',
                        'help_text' => 'The username provided by Africa\'s Talking',
                    ],
                    'encrypted' => true,
                ]
            ],
            [
                'name' => 'AFRICASTALKING_API_KEY',
                '_slug' => 'africastalking-api-key',
                'description' => 'Africa\'s Talking API key',
                'default_value' => '',
                'current_value' => env('AFRICASTALKING_API_KEY', ''),
                'data_type' => 'string',
                'group' => 'africastalking',
                'is_public' => false,
                'options' => [
                    'type' => 'password',
                    'validation' => ['required', 'string'],
                    'ui' => [
                        'component' => 'password-input',
                        'placeholder' => 'Enter your Africa\'s Talking API key',
                        'help_text' => 'The API key from your Africa\'s Talking account',
                    ],
                    'encrypted' => true,
                ]
            ],
            [
                'name' => 'AFRICASTALKING_SENDER_ID',
                '_slug' => 'africastalking-sender-id',
                'description' => 'Default sender ID for SMS messages',
                'default_value' => env('APP_NAME', 'Rebirth'),
                'current_value' => env('AFRICASTALKING_SENDER_ID', env('APP_NAME', 'Rebirth')),
                'data_type' => 'string',
                'group' => 'africastalking',
                'is_public' => false,
                'options' => [
                    'type' => 'text',
                    'validation' => ['required', 'string', 'max:11'],
                    'ui' => [
                        'component' => 'text-input',
                        'placeholder' => 'e.g., REBIRTH',
                        'help_text' => 'Max 11 characters, alphanumeric only',
                    ]
                ]
            ],
            [
                'name' => 'AFRICASTALKING_ENABLED',
                '_slug' => 'africastalking-enabled',
                'description' => 'Enable/Disable Africa\'s Talking service',
                'default_value' => 'false',
                'current_value' => env('AFRICASTALKING_ENABLED', 'false'),
                'data_type' => 'boolean',
                'group' => 'africastalking',
                'is_public' => false,
                'options' => [
                    'type' => 'toggle',
                    'validation' => ['boolean'],
                    'ui' => [
                        'component' => 'toggle',
                        'help_text' => 'Enable or disable Africa\'s Talking service',
                    ]
                ]
            ]
        ];

        foreach ($settings as $setting) {
            Setting::updateOrCreate(
                ['name' => $setting['name']],
                $setting
            );
        }
    }
}
