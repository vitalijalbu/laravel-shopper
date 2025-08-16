<?php

namespace LaravelShopper\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use LaravelShopper\Models\User;

class TestLoginCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'shopper:test-login {email} {password}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test login credentials';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $email = $this->argument('email');
        $password = $this->argument('password');

        $this->info("🔍 Testing login for: {$email}");

        // Find user
        $user = User::where('email', $email)->first();

        if (!$user) {
            $this->error("❌ User with email '{$email}' not found");
            
            // Show available users
            $users = User::all(['email', 'first_name', 'last_name']);
            if ($users->isNotEmpty()) {
                $this->info("Available users:");
                foreach ($users as $u) {
                    $this->line("  - {$u->email} ({$u->first_name} {$u->last_name})");
                }
            }
            
            return 1;
        }

        $this->info("✅ User found: {$user->first_name} {$user->last_name}");

        // Test password
        if (Hash::check($password, $user->password)) {
            $this->info("✅ Password is correct");
            
            // Test roles
            if (method_exists($user, 'getRoleNames')) {
                $roles = $user->getRoleNames();
                $this->info("User roles: " . ($roles->isEmpty() ? 'No roles' : $roles->implode(', ')));
            }
            
            return 0;
        } else {
            $this->error("❌ Password is incorrect");
            $this->line("💡 Hint: Default password is usually 'password'");
            return 1;
        }
    }
}
