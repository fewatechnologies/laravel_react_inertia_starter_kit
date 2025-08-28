<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\MasterAdmin\User;
use Illuminate\Support\Facades\Hash;

class CreateMasterAdmin extends Command
{
    protected $signature = 'master-admin:create 
                          {--email= : Admin email address}
                          {--password= : Admin password}
                          {--name= : Admin name}';

    protected $description = 'Create a master admin user';

    public function handle()
    {
        $email = $this->option('email') ?: $this->ask('Admin Email');
        $name = $this->option('name') ?: $this->ask('Admin Name', 'Master Admin');
        $password = $this->option('password') ?: $this->secret('Admin Password');

        if (User::where('email', $email)->exists()) {
            $this->error('Admin user with this email already exists.');
            return 1;
        }

        $user = User::create([
            'name' => $name,
            'email' => $email,
            'password' => Hash::make($password),
            'is_active' => true,
        ]);

        $this->info('Master admin user created successfully!');
        $this->table(['Field', 'Value'], [
            ['Name', $user->name],
            ['Email', $user->email],
            ['Login URL', route('master-admin.login')],
        ]);

        return 0;
    }
}
