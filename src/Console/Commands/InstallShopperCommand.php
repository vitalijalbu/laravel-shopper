<?php

namespace LaravelShopper\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

class InstallShopperCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'shopper:install 
                          {--oauth : Install OAuth authentication system}
                          {--force : Overwrite existing files}
                          {--seed : Run database seeders}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Install Laravel Shopper package';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('🛍️  Installing Laravel Shopper...');

        // Check Laravel version
        if (! $this->checkLaravelVersion()) {
            return 1;
        }

        // Publish configuration
        $this->publishConfiguration();

        // Publish OAuth system if requested
        if ($this->option('oauth')) {
            $this->publishOAuthSystem();
        }

        // Run migrations
        $this->runMigrations();

        // Publish assets
        $this->publishAssets();

        // Run seeders if requested
        if ($this->option('seed')) {
            $this->runSeeders();
        }

        // Display completion message
        $this->displayCompletionMessage();

        return 0;
    }

    /**
     * Check Laravel version compatibility.
     */
    protected function checkLaravelVersion(): bool
    {
        $laravelVersion = app()->version();

        if (version_compare($laravelVersion, '11.0', '<')) {
            $this->error('❌ Laravel Shopper requires Laravel 11.0 or higher.');
            $this->error("   Current version: {$laravelVersion}");

            return false;
        }

        $this->info("✅ Laravel version {$laravelVersion} is compatible.");

        return true;
    }

    /**
     * Publish configuration files.
     */
    protected function publishConfiguration(): void
    {
        $this->info('📝 Publishing configuration files...');

        $params = ['--provider' => 'LaravelShopper\ShopperServiceProvider'];

        if ($this->option('force')) {
            $params['--force'] = true;
        }

        Artisan::call('vendor:publish', array_merge($params, ['--tag' => 'shopper-config']));

        $this->info('✅ Configuration published successfully.');
    }

    /**
     * Publish OAuth authentication system.
     */
    protected function publishOAuthSystem(): void
    {
        $this->info('🔐 Publishing OAuth authentication system...');

        $params = ['--provider' => 'LaravelShopper\ShopperServiceProvider'];

        if ($this->option('force')) {
            $params['--force'] = true;
        }

        // Publish OAuth configuration
        Artisan::call('vendor:publish', array_merge($params, ['--tag' => 'shopper-oauth-config']));

        // Publish Vue components
        Artisan::call('vendor:publish', array_merge($params, ['--tag' => 'shopper-components']));

        // Publish translations
        Artisan::call('vendor:publish', array_merge($params, ['--tag' => 'shopper-lang']));

        $this->info('✅ OAuth system published successfully.');

        // Display OAuth setup instructions
        $this->displayOAuthInstructions();
    }

    /**
     * Run database migrations.
     */
    protected function runMigrations(): void
    {
        $this->info('🗄️  Running database migrations...');

        if ($this->confirm('Do you want to run migrations now?', true)) {
            Artisan::call('migrate');
            $this->info('✅ Migrations completed successfully.');
        } else {
            $this->warn('⚠️  Skipped migrations. Run "php artisan migrate" manually when ready.');
        }
    }

    /**
     * Publish assets.
     */
    protected function publishAssets(): void
    {
        $this->info('🎨 Publishing assets...');

        $params = ['--provider' => 'LaravelShopper\ShopperServiceProvider'];

        if ($this->option('force')) {
            $params['--force'] = true;
        }

        Artisan::call('vendor:publish', array_merge($params, ['--tag' => 'shopper-assets']));
        Artisan::call('vendor:publish', array_merge($params, ['--tag' => 'shopper-views']));

        $this->info('✅ Assets published successfully.');
    }

    /**
     * Run database seeders.
     */
    protected function runSeeders(): void
    {
        $this->info('🌱 Running database seeders...');

        if (class_exists('LaravelShopper\Database\Seeders\ShopperSeeder')) {
            Artisan::call('db:seed', ['--class' => 'LaravelShopper\Database\Seeders\ShopperSeeder']);
            $this->info('✅ Seeders completed successfully.');
        } else {
            $this->warn('⚠️  No seeders found.');
        }
    }

    /**
     * Display OAuth setup instructions.
     */
    protected function displayOAuthInstructions(): void
    {
        $this->newLine();
        $this->info('🔐 OAuth Setup Instructions:');
        $this->line('');
        $this->line('1. Add OAuth provider credentials to your .env file:');
        $this->line('   GOOGLE_CLIENT_ID=your_google_client_id');
        $this->line('   GOOGLE_CLIENT_SECRET=your_google_client_secret');
        $this->line('   FACEBOOK_CLIENT_ID=your_facebook_client_id');
        $this->line('   FACEBOOK_CLIENT_SECRET=your_facebook_client_secret');
        $this->line('   (... and so on for other providers)');
        $this->line('');
        $this->line('2. Configure OAuth callbacks in your provider apps:');
        $this->line('   - Google: https://yourapp.com/auth/social/google/callback');
        $this->line('   - Facebook: https://yourapp.com/auth/social/facebook/callback');
        $this->line('   - GitHub: https://yourapp.com/auth/social/github/callback');
        $this->line('   (... and so on for other providers)');
        $this->line('');
        $this->line('3. Use the SocialAuthComponent in your Vue.js templates');
        $this->line('');
        $this->info('📖 See OAUTH_SETUP.md for detailed instructions.');
    }

    /**
     * Display installation completion message.
     */
    protected function displayCompletionMessage(): void
    {
        $this->newLine();
        $this->info('🎉 Laravel Shopper installation completed successfully!');
        $this->line('');

        if ($this->option('oauth')) {
            $this->line('✅ OAuth authentication system installed');
        }

        $this->line('✅ Configuration published');
        $this->line('✅ Assets published');
        $this->line('✅ Database migrations ready');
        $this->line('');
        $this->info('🚀 Next steps:');
        $this->line('1. Configure your environment variables');

        if ($this->option('oauth')) {
            $this->line('2. Set up OAuth provider credentials');
            $this->line('3. Configure OAuth callback URLs');
        }

        $this->line('4. Start building your e-commerce application!');
        $this->line('');
        $this->line('📖 Documentation: https://github.com/vitalijalbu/laravel-shopper');
    }
}
