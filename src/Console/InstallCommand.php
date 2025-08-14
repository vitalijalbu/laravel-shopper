<?php

namespace LaravelShopper\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

class InstallCommand extends Command
{
    protected $signature = 'shopper:install {--force : Overwrite existing files}';

    protected $description = 'Install Laravel Shopper package';

    public function handle(): int
    {
        $this->info('Installing Laravel Shopper...');

        // Publish config
        $this->info('Publishing configuration...');
        Artisan::call('vendor:publish', [
            '--tag' => 'shopper-config',
            '--force' => $this->option('force'),
        ]);

        // Publish migrations
        $this->info('Publishing migrations...');
        Artisan::call('vendor:publish', [
            '--tag' => 'shopper-core-migrations',
            '--force' => $this->option('force'),
        ]);

        // Run migrations
        if ($this->confirm('Would you like to run the migrations now?')) {
            $this->info('Running migrations...');
            Artisan::call('migrate');
            $this->info('Migrations completed.');
        }

        // Run seeders
        if ($this->confirm('Would you like to seed the database with sample data?')) {
            $this->info('Seeding database...');
            Artisan::call('db:seed', [
                '--class' => 'LaravelShopper\\Database\\Seeders\\ShopperSeeder',
            ]);
            $this->info('Database seeded.');
        }

        // Create admin user
        if ($this->confirm('Would you like to create an admin user?')) {
            $this->createAdminUser();
        }

        $this->info('✅ Laravel Shopper has been installed successfully!');
        $this->info('');
        $this->info('Next steps:');
        $this->info('1. Configure your .env file with database settings');
        $this->info('2. Visit /admin to access the admin panel');
        $this->info('3. Check the documentation at: https://github.com/vitalijalbu/laravel-shopper');

        return self::SUCCESS;
    }

    private function createAdminUser(): void
    {
        $userModel = config('shopper.auth.model', 'App\\Models\\User');
        
        if (!class_exists($userModel)) {
            $this->error("User model {$userModel} not found. Please create it first.");
            return;
        }

        $name = $this->ask('Admin name', 'Admin');
        $email = $this->ask('Admin email', 'admin@example.com');
        $password = $this->secret('Admin password');

        if (!$password) {
            $password = 'password';
            $this->info('Using default password: password');
        }

        $user = $userModel::create([
            'name' => $name,
            'email' => $email,
            'password' => bcrypt($password),
            'email_verified_at' => now(),
        ]);

        // If Spatie Permissions is installed, assign admin role/permissions
        if (method_exists($user, 'givePermissionTo')) {
            try {
                $permissions = [
                    'manage-products',
                    'manage-orders',
                    'manage-customers',
                    'manage-categories',
                    'manage-brands',
                    'manage-discounts',
                    'manage-settings',
                ];

                foreach ($permissions as $permission) {
                    if (!\Spatie\Permission\Models\Permission::where('name', $permission)->exists()) {
                        \Spatie\Permission\Models\Permission::create(['name' => $permission]);
                    }
                }

                $user->givePermissionTo($permissions);
                $this->info('Admin permissions assigned.');
            } catch (\Exception $e) {
                $this->warn('Could not assign permissions: ' . $e->getMessage());
            }
        }

        $this->info("Admin user created: {$email}");
    }
}
