<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create permissions
        $permissions = [
            // Book permissions
            'view-books',
            'create-books',
            'update-books',
            'delete-books',
            
            // Category permissions
            'view-categories',
            'create-categories',
            'update-categories',
            'delete-categories',
            
            // Loan permissions
            'view-loans',
            'create-loans',
            'update-loans',
            'delete-loans',
            'manage-overdue',
            
            // User permissions
            'view-users',
            'create-users',
            'update-users',
            'delete-users',
            
            // Report permissions
            'view-statistics',
            'export-data',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // Create roles
        $adminRole = Role::firstOrCreate(['name' => 'admin']);
        $librarianRole = Role::firstOrCreate(['name' => 'librarian']);
        $memberRole = Role::firstOrCreate(['name' => 'member']);

        // Assign permissions to admin (all permissions)
        $adminRole->givePermissionTo(Permission::all());

        // Assign permissions to librarian
        $librarianRole->givePermissionTo([
            'view-books', 'create-books', 'update-books',
            'view-categories', 'create-categories', 'update-categories',
            'view-loans', 'create-loans', 'update-loans', 'manage-overdue',
            'view-users', 'update-users',
            'view-statistics', 'export-data',
        ]);

        // Assign permissions to member
        $memberRole->givePermissionTo([
            'view-books',
            'view-categories',
            'create-loans', // Can borrow books
        ]);
    }
}
