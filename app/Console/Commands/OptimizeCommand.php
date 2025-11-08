<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

class OptimizeCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:optimize';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Optimizing the application for better performance';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting optimization process...');
        
        try {
            // Run composer commands
            $this->info('Running Composer commands...');
            $this->runComposerCommands();
            
            // Run Laravel optimization commands
            $this->info('Running Laravel optimization...');
            // $this->runLaravelOptimization();
            
            $this->info('Optimization completed successfully!');
            return Command::SUCCESS;
            
        } catch (\Throwable $th) {
            // throw $th;
            $this->error('Optimization failed: ' . $th->getMessage());
            $this->error($th->getTraceAsString());
            return Command::FAILURE;
        }
    }
    
    /**
     * Run Composer commands
     */
    protected function runComposerCommands()
    {
        $commands = [
            'composer dump-autoload',
        ];
        
        foreach ($commands as $command) {
            $this->info("Running: $command");
            $output = [];
            $returnVar = 0;
            
            exec("cd " . base_path() . " && $command 2>&1", $output, $returnVar);
            
            if ($returnVar !== 0) {
                throw new \RuntimeException("Command failed: $command\n" . implode("\n", $output));
            }
            
            $this->line(implode("\n", $output));
        }
    }
    
    /**
     * Run Laravel optimization commands
     */
    protected function runLaravelOptimization()
    {
        $commands = [
            'migrate --force --seed', // Run migrations to handle database updates
            'config:cache',
            'route:cache',
            'view:cache',
            'event:cache',
            'optimize',
        ];
        
        foreach ($commands as $command) {
            $this->info("Running: php artisan $command");
            Artisan::call($command);
        }
    }
}