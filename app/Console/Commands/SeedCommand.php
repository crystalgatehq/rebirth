<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class SeedCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:seed';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate seeders for application tables using iSeed';

    /**
     * The list of tables to generate seeders for.
     *
     * @var array
     */
    protected $tables = [
        // 'settings',
        'roles',
        'abilities',
        'ability_role'
    ];

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting seeder generation...');
        
        try {
            $this->info('Generating seeders for tables: ' . implode(', ', $this->tables));
            
            // Generate seeders using iSeed from orangehill/iseed package
            $this->call('iseed', [
                'tables' => implode(',', $this->tables),
                '--force' => true,
            ]);
            
            $this->info('Seeder generation completed successfully!');
            $this->info('Seeders are available in: database/seeders/*_*_*_*_*_*_table_seeder.php');
            
            return Command::SUCCESS;
            
        } catch (\Throwable $th) {
            // throw $th;
            $this->error('Error generating seeders: ' . $th->getMessage());
            
            // if (function_exists('eThrowable')) {
            //     eThrowable(get_class($this), $th->getMessage(), $th->getTraceAsString());
            // } else {
            //     $this->error($th->getTraceAsString());
            // }
            
            return Command::FAILURE;
        }
    }
}
