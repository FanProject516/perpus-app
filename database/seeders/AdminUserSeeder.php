<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create admin user
        $admin = User::create([
            'name' => 'Administrator',
            'email' => 'admin@perpus.local',
            'password' => Hash::make('password123'),
            'phone' => '08123456789',
            'address' => 'Kantor Perpustakaan',
            'membership_date' => now(),
            'is_active' => true,
        ]);
        $admin->assignRole('admin');

        // Create librarian user
        $librarian = User::create([
            'name' => 'Pustakawan',
            'email' => 'librarian@perpus.local',
            'password' => Hash::make('password123'),
            'phone' => '08123456788',
            'address' => 'Kantor Perpustakaan',
            'membership_date' => now(),
            'is_active' => true,
        ]);
        $librarian->assignRole('librarian');

        // Create sample member users
        $members = [
            [
                'name' => 'John Doe',
                'email' => 'john@example.com',
                'student_id' => 'STD001',
                'phone' => '08111111111',
                'address' => 'Jl. Contoh No. 1'
            ],
            [
                'name' => 'Jane Smith',
                'email' => 'jane@example.com',
                'student_id' => 'STD002',
                'phone' => '08222222222',
                'address' => 'Jl. Contoh No. 2'
            ],
            [
                'name' => 'Bob Wilson',
                'email' => 'bob@example.com',
                'student_id' => 'STD003',
                'phone' => '08333333333',
                'address' => 'Jl. Contoh No. 3'
            ]
        ];

        foreach ($members as $memberData) {
            $member = User::create([
                'name' => $memberData['name'],
                'email' => $memberData['email'],
                'password' => Hash::make('password123'),
                'phone' => $memberData['phone'],
                'address' => $memberData['address'],
                'student_id' => $memberData['student_id'],
                'membership_date' => now(),
                'is_active' => true,
            ]);
            $member->assignRole('member');
        }
    }
}
