<?php

namespace Database\Seeders;

use App\Models\Admin;
use Illuminate\Database\Seeder;
use RuntimeException;

/**
 * Creates (or promotes) a super_admin account. Safe to run on production:
 *
 *   php artisan db:seed --class=SuperAdminSeeder --force
 *
 * Credentials come from SUPER_ADMIN_EMAIL / SUPER_ADMIN_PASSWORD /
 * SUPER_ADMIN_NAME, or are prompted for when running interactively.
 * Re-running for an existing email promotes it to super_admin and
 * re-activates it, but only changes the password if one is supplied.
 */
class SuperAdminSeeder extends Seeder
{
    private const MIN_PASSWORD_LENGTH = 10;

    public function run(): void
    {
        $email = env('SUPER_ADMIN_EMAIL') ?: $this->command?->ask('Super admin email');

        if (! $email || ! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new RuntimeException('A valid SUPER_ADMIN_EMAIL is required.');
        }

        $existing = Admin::where('email', $email)->first();

        $name = env('SUPER_ADMIN_NAME')
            ?: $existing?->name
            ?: $this->command?->ask('Super admin name', 'Super Admin')
            ?: 'Super Admin';

        $password = env('SUPER_ADMIN_PASSWORD')
            ?: $this->command?->secret($existing ? 'New password (leave blank to keep current)' : 'Super admin password');

        if (! $existing && ! $password) {
            throw new RuntimeException('SUPER_ADMIN_PASSWORD is required to create a new super admin.');
        }

        if ($password && strlen($password) < self::MIN_PASSWORD_LENGTH) {
            throw new RuntimeException('Super admin password must be at least ' . self::MIN_PASSWORD_LENGTH . ' characters.');
        }

        $attributes = [
            'name'      => $name,
            'role'      => 'super_admin',
            'is_active' => true,
        ];

        if ($password) {
            $attributes['password'] = $password; // hashed by the Admin model cast
        }

        $admin = Admin::updateOrCreate(['email' => $email], $attributes);

        $this->command?->info(sprintf(
            '%s super admin %s (id %d)%s.',
            $admin->wasRecentlyCreated ? 'Created' : 'Updated',
            $admin->email,
            $admin->id,
            $password || $admin->wasRecentlyCreated ? '' : ', password unchanged'
        ));
    }
}
