<?php

namespace Database\Seeders;

use App\Models\Tenant;
use App\Models\User;
use App\Models\Flag;
use App\Models\Group;
use App\Models\Targeting;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Create demo tenant
        $tenant = Tenant::factory()->create([
            'name' => 'Demo Company',
            'email' => 'admin@demo.com',
        ]);

        // Create admin user
        $user = User::factory()->create([
            'tenant_id' => $tenant->id,
            'name' => 'Demo Admin',
            'email' => 'admin@demo.com',
            'password' => Hash::make('password'),
        ]);

        // Create groups
        $betaTesters = Group::create([
            'tenant_id' => $tenant->id,
            'identifier' => 'beta-testers',
            'name' => 'Beta Testers',
            'description' => 'Users enrolled in the beta testing program',
        ]);

        $premiumUsers = Group::create([
            'tenant_id' => $tenant->id,
            'identifier' => 'premium-users',
            'name' => 'Premium Users',
            'description' => 'Users with premium subscription',
        ]);

        $internalTeam = Group::create([
            'tenant_id' => $tenant->id,
            'identifier' => 'internal-team',
            'name' => 'Internal Team',
            'description' => 'Internal employees and contractors',
        ]);

        // Create flags
        $darkMode = Flag::create([
            'tenant_id' => $tenant->id,
            'key' => 'dark-mode',
            'name' => 'Dark Mode',
            'description' => 'Enable dark mode theme for the application',
            'is_enabled' => true,
        ]);

        $newDashboard = Flag::create([
            'tenant_id' => $tenant->id,
            'key' => 'new-dashboard',
            'name' => 'New Dashboard',
            'description' => 'New redesigned dashboard experience',
            'is_enabled' => true,
        ]);

        $aiFeatures = Flag::create([
            'tenant_id' => $tenant->id,
            'key' => 'ai-features',
            'name' => 'AI Features',
            'description' => 'Experimental AI-powered features',
            'is_enabled' => false,
        ]);

        $exportPdf = Flag::create([
            'tenant_id' => $tenant->id,
            'key' => 'export-pdf',
            'name' => 'PDF Export',
            'description' => 'Allow exporting reports as PDF',
            'is_enabled' => true,
        ]);

        $notifications = Flag::create([
            'tenant_id' => $tenant->id,
            'key' => 'push-notifications',
            'name' => 'Push Notifications',
            'description' => 'Enable browser push notifications',
            'is_enabled' => false,
        ]);

        // Create targeting rules
        // New Dashboard: only for beta testers and internal team
        Targeting::create(['flag_id' => $newDashboard->id, 'group_id' => $betaTesters->id]);
        Targeting::create(['flag_id' => $newDashboard->id, 'group_id' => $internalTeam->id]);

        // AI Features: only for internal team (testing)
        Targeting::create(['flag_id' => $aiFeatures->id, 'group_id' => $internalTeam->id]);

        // Push Notifications: for premium users
        Targeting::create(['flag_id' => $notifications->id, 'group_id' => $premiumUsers->id]);

        $this->command->info('Demo data seeded successfully!');
        $this->command->info('Login: admin@demo.com / password');
    }
}
