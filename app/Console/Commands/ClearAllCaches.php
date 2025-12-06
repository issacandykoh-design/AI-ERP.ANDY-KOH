<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class ClearAllCaches extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cache:clear-all';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clear all Laravel caches (application, config, route, view, compiled)';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('Clearing all Laravel caches...');
        $this->newLine();

        $this->line('[1/6] Clearing application cache...');
        $this->call('cache:clear');
        $this->newLine();

        $this->line('[2/6] Clearing configuration cache...');
        $this->call('config:clear');
        $this->newLine();

        $this->line('[3/6] Clearing route cache...');
        $this->call('route:clear');
        $this->newLine();

        $this->line('[4/6] Clearing view cache...');
        $this->call('view:clear');
        $this->newLine();

        $this->line('[5/6] Clearing compiled class cache...');
        $this->call('clear-compiled');
        $this->newLine();

        $this->line('[6/6] Optimizing application...');
        $this->call('optimize:clear');
        $this->newLine();

        $this->info('✓ All caches cleared successfully!');
        $this->newLine();

        return Command::SUCCESS;
    }
}
