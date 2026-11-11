<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;

class RolePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $permissions = [
            // Orders
            ['slug' => 'orders.view', 'name' => 'Lihat Order Servis', 'group' => 'orders'],
            ['slug' => 'orders.create', 'name' => 'Buat Order Servis', 'group' => 'orders'],
            ['slug' => 'orders.edit', 'name' => 'Ubah Order Servis', 'group' => 'orders'],
            ['slug' => 'orders.delete', 'name' => 'Hapus Order Servis', 'group' => 'orders'],
            ['slug' => 'orders.status.update', 'name' => 'Update Status Pengerjaan', 'group' => 'orders'],

            // Customers
            ['slug' => 'customers.view', 'name' => 'Lihat Pelanggan', 'group' => 'customers'],
            ['slug' => 'customers.manage', 'name' => 'Kelola Data Pelanggan', 'group' => 'customers'],

            // Services Catalog
            ['slug' => 'services.view', 'name' => 'Lihat Katalog Layanan', 'group' => 'services'],
            ['slug' => 'services.manage', 'name' => 'Kelola Katalog Layanan', 'group' => 'services'],

            // Inventory
            ['slug' => 'inventory.view', 'name' => 'Lihat Stok & Sparepart', 'group' => 'inventory'],
            ['slug' => 'inventory.manage', 'name' => 'Kelola Produk & Sparepart', 'group' => 'inventory'],
            ['slug' => 'inventory.stock.adjust', 'name' => 'Penyesuaian Stok (Opname)', 'group' => 'inventory'],

            // Suppliers
            ['slug' => 'suppliers.view', 'name' => 'Lihat Supplier', 'group' => 'suppliers'],
            ['slug' => 'suppliers.manage', 'name' => 'Kelola Supplier & Pembelian', 'group' => 'suppliers'],

            // Finances
            ['slug' => 'finances.view', 'name' => 'Lihat Laporan Keuangan', 'group' => 'finances'],
            ['slug' => 'finances.manage', 'name' => 'Kelola Kas & Transaksi', 'group' => 'finances'],

            // Reports
            ['slug' => 'reports.view', 'name' => 'Lihat Laporan & Analisis', 'group' => 'reports'],
            ['slug' => 'reports.export', 'name' => 'Export Laporan', 'group' => 'reports'],

            // Technicians
            ['slug' => 'technicians.manage', 'name' => 'Kelola Teknisi & Komisi', 'group' => 'technicians'],

            // Settings
            ['slug' => 'store.settings', 'name' => 'Kelola Pengaturan Toko', 'group' => 'settings'],
            ['slug' => 'subscriptions.manage', 'name' => 'Kelola Paket Langganan', 'group' => 'settings'],

            // Platform (Super Admin)
            ['slug' => 'platform.manage', 'name' => 'Kelola Platform Super Admin', 'group' => 'platform'],
        ];

        foreach ($permissions as $perm) {
            Permission::firstOrCreate(['slug' => $perm['slug']], $perm);
        }

        // Roles
        $ownerRole = Role::firstOrCreate(
            ['slug' => 'owner'],
            ['name' => 'Owner (Pemilik Toko)', 'description' => 'Akses penuh mengelola seluruh operasional toko dan langganan.']
        );

        $adminRole = Role::firstOrCreate(
            ['slug' => 'admin'],
            ['name' => 'Admin / Frontdesk', 'description' => 'Mengelola penerimaan unit, order servis, kasir, dan pelanggan.']
        );

        $techRole = Role::firstOrCreate(
            ['slug' => 'technician'],
            ['name' => 'Teknisi Servis', 'description' => 'Mengerjakan reparasi unit, diagnosa, dan update progres pengerjaan.']
        );

        $superAdminRole = Role::firstOrCreate(
            ['slug' => 'super_admin'],
            ['name' => 'Super Admin Platform', 'description' => 'Pengelola platform SEMAPON SaaS keseluruhan.']
        );

        // Assign Permissions:
        // 1. Owner gets all store permissions (except platform.manage)
        $ownerPermissions = Permission::where('group', '!=', 'platform')->pluck('id');
        $ownerRole->permissions()->sync($ownerPermissions);

        // 2. Admin gets operational permissions
        $adminPermissions = Permission::whereIn('slug', [
            'orders.view', 'orders.create', 'orders.edit', 'orders.status.update',
            'customers.view', 'customers.manage',
            'services.view',
            'inventory.view',
            'suppliers.view',
            'finances.view', // Basic cash register intake
        ])->pluck('id');
        $adminRole->permissions()->sync($adminPermissions);

        // 3. Technician gets service execution permissions
        $techPermissions = Permission::whereIn('slug', [
            'orders.view',
            'orders.status.update',
            'services.view',
            'inventory.view', // Can view sparepart availability
        ])->pluck('id');
        $techRole->permissions()->sync($techPermissions);

        // 4. Super Admin gets platform permissions
        $superAdminPermissions = Permission::whereIn('slug', [
            'platform.manage',
            'reports.view',
        ])->pluck('id');
        $superAdminRole->permissions()->sync($superAdminPermissions);
    }
}
