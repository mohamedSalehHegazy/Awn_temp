<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Str;

class AdminCrudBuilder extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'crud:build {modelName}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Creates Full API Crud for the specified model';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        // make ListResource
        Artisan::call('make:resource', [
            'name' => 'Admin/' . Str::plural($this->argument('modelName')) . 'ListResource',
        ]);

        // make SingleResource
        Artisan::call('make:resource', [
            'name' => 'Admin/' . Str::plural($this->argument('modelName')) . 'SingleResource',
        ]);

        // make CreateRequest
        Artisan::call('make:request', [
            'name' => 'Admin/Create' . Str::plural($this->argument('modelName')) . 'Request',
        ]);

        // make UpdateRequest
        Artisan::call('make:request', [
            'name' => 'Admin/Update' . Str::plural($this->argument('modelName')) . 'Request',
        ]);

        // make Routes
        Artisan::call('make:AdminRoutes', [
            'name' => $this->argument('modelName'),
        ]);

        // make Controller
        Artisan::call('make:AdminController', [
            'name' => $this->argument('modelName'),
        ]);

        // make CRUD PermissionSeeder
        Artisan::call('make:AdminPermissionSeeder', [
            'name' => $this->argument('modelName'),
        ]);

        // make CRUD AdminPostmanCollection
        Artisan::call('make:AdminPostmanCollection', [
            'name' => $this->argument('modelName'),
        ]);
    }
}
