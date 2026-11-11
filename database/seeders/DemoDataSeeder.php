<?php

namespace Database\Seeders;

use App\Models\Customer;
use App\Models\CustomerDevice;
use App\Models\Payment;
use App\Models\Product;
use App\Models\Service;
use App\Models\ServiceCategory;
use App\Models\ServiceOrder;
use App\Models\ServiceTrackingToken;
use App\Models\Store;
use App\Models\StoreMembership;
use App\Models\Supplier;
use App\Models\TechnicianProfile;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DemoDataSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            RolePermissionSeeder::class,
            PlanSeeder::class,
        ]);

        $defaultPassword = Hash::make('password');

        // 1. Super Admin User & Platform HQ
        $superAdmin = User::firstOrCreate(
            ['email' => 'superadmin@semapon.com'],
            [
                'name' => 'Super Admin SEMAPON',
                'phone' => '081000000001',
                'password' => $defaultPassword,
                'status' => 'active',
                'email_verified_at' => now(),
            ]
        );

        $platformStore = Store::firstOrCreate(
            ['slug' => 'platform-hq'],
            [
                'name' => 'SEMAPON Platform HQ',
                'owner_id' => $superAdmin->id,
                'status' => 'active',
                'plan' => 'pro',
            ]
        );

        StoreMembership::firstOrCreate(
            ['store_id' => $platformStore->id, 'user_id' => $superAdmin->id],
            [
                'role' => 'super_admin',
                'status' => 'active',
                'joined_at' => now(),
            ]
        );

        // 2. Demo Store Owner
        $owner = User::firstOrCreate(
            ['email' => 'owner@semapon.com'],
            [
                'name' => 'Budi Santoso (Owner)',
                'phone' => '081234567890',
                'password' => $defaultPassword,
                'status' => 'active',
                'email_verified_at' => now(),
            ]
        );

        $store = Store::firstOrCreate(
            ['slug' => 'mizutech-phone-care'],
            [
                'name' => 'Mizutech Phone Care',
                'owner_id' => $owner->id,
                'phone' => '081234567890',
                'email' => 'care@mizutech.id',
                'address' => 'Jl. Soekarno Hatta No. 45, Kota Malang, Jawa Timur',
                'status' => 'active',
                'plan' => 'business',
                'trial_ends_at' => now()->addDays(14),
                'subscription_ends_at' => now()->addMonths(1),
            ]
        );

        StoreMembership::firstOrCreate(
            ['store_id' => $store->id, 'user_id' => $owner->id],
            [
                'role' => 'owner',
                'status' => 'active',
                'joined_at' => now(),
            ]
        );

        // 3. Technicians
        $tech1 = User::firstOrCreate(
            ['email' => 'teknisi@semapon.com'],
            [
                'name' => 'Rian Hidayat (Teknisi)',
                'phone' => '081234567892',
                'password' => $defaultPassword,
                'status' => 'active',
                'email_verified_at' => now(),
            ]
        );

        $membershipTech1 = StoreMembership::firstOrCreate(
            ['store_id' => $store->id, 'user_id' => $tech1->id],
            [
                'role' => 'technician',
                'status' => 'active',
                'joined_at' => now(),
            ]
        );

        TechnicianProfile::firstOrCreate(
            ['store_id' => $store->id, 'user_id' => $tech1->id],
            [
                'membership_id' => $membershipTech1->id,
                'specialization' => 'Hardware & Mesin',
                'commission_rate' => 20.00,
            ]
        );

        $tech2 = User::firstOrCreate(
            ['email' => 'doni@semapon.com'],
            [
                'name' => 'Doni Kusuma (Teknisi)',
                'phone' => '081234567893',
                'password' => $defaultPassword,
                'status' => 'active',
                'email_verified_at' => now(),
            ]
        );

        $membershipTech2 = StoreMembership::firstOrCreate(
            ['store_id' => $store->id, 'user_id' => $tech2->id],
            [
                'role' => 'technician',
                'status' => 'active',
                'joined_at' => now(),
            ]
        );

        TechnicianProfile::firstOrCreate(
            ['store_id' => $store->id, 'user_id' => $tech2->id],
            [
                'membership_id' => $membershipTech2->id,
                'specialization' => 'Layar & Port Charger',
                'commission_rate' => 15.00,
            ]
        );

        // 4. Service Catalog
        $catHardware = ServiceCategory::firstOrCreate(
            ['store_id' => $store->id, 'name' => 'Hardware & Layar'],
            ['description' => 'Servis komponen fisik layar, konektor, dan casing.']
        );

        $serviceLcd = Service::firstOrCreate(
            ['store_id' => $store->id, 'name' => 'Ganti LCD / Touchscreen'],
            [
                'category_id' => $catHardware->id,
                'price' => 150000,
                'estimated_duration_minutes' => 60,
                'description' => 'Jasa pemasangan LCD baru rapi dan presisi.',
            ]
        );

        $servicePort = Service::firstOrCreate(
            ['store_id' => $store->id, 'name' => 'Servis / Ganti Port Charger'],
            [
                'category_id' => $catHardware->id,
                'price' => 100000,
                'estimated_duration_minutes' => 45,
                'description' => 'Perbaikan konektor cas longgar atau tidak mengisi daya.',
            ]
        );

        // 5. Inventory & Spareparts
        Product::firstOrCreate(
            ['store_id' => $store->id, 'sku' => 'LCD-IP13P-ORI'],
            [
                'name' => 'LCD iPhone 13 Pro OLED Original',
                'stock' => 4,
                'min_stock' => 2,
                'buy_price' => 1100000,
                'sell_price' => 1700000,
                'type' => 'sparepart',
                'unit' => 'pcs',
                'is_active' => true,
            ]
        );

        Product::firstOrCreate(
            ['store_id' => $store->id, 'sku' => 'BAT-IP13-ORI'],
            [
                'name' => 'Baterai iPhone 13 Original',
                'stock' => 8,
                'min_stock' => 3,
                'buy_price' => 1800000 / 10,
                'sell_price' => 350000,
                'type' => 'sparepart',
                'unit' => 'pcs',
                'is_active' => true,
            ]
        );

        Product::firstOrCreate(
            ['store_id' => $store->id, 'sku' => 'PORT-S23-USB'],
            [
                'name' => 'Flex Port Charger Samsung Galaxy S23 Type-C',
                'stock' => 10,
                'min_stock' => 4,
                'buy_price' => 45000,
                'sell_price' => 150000,
                'type' => 'sparepart',
                'unit' => 'pcs',
                'is_active' => true,
            ]
        );

        // Low stock products to trigger alerts on dashboard
        Product::firstOrCreate(
            ['store_id' => $store->id, 'sku' => 'LCD-RED-12'],
            [
                'name' => 'LCD Xiaomi Redmi Note 12 Original',
                'stock' => 1,
                'min_stock' => 3,
                'buy_price' => 200000,
                'sell_price' => 350000,
                'type' => 'sparepart',
                'unit' => 'pcs',
                'is_active' => true,
            ]
        );

        // 6. Supplier
        Supplier::firstOrCreate(
            ['store_id' => $store->id, 'name' => 'CV Sentral Sparepart Jakarta'],
            [
                'contact_person' => 'Koh David',
                'phone' => '081912345678',
                'address' => 'Roxy Mas Lantai 2 No. 88, Jakarta Pusat',
            ]
        );

        // 7. Customers & Devices
        $customer1 = Customer::firstOrCreate(
            ['store_id' => $store->id, 'phone' => '081234567891'],
            ['name' => 'Ahmad Dahlan', 'address' => 'Malang']
        );
        $device1 = CustomerDevice::firstOrCreate(
            ['store_id' => $store->id, 'customer_id' => $customer1->id, 'model' => 'iPhone 13 Pro'],
            ['brand' => 'Apple', 'color' => 'Midnight Blue']
        );

        $customer2 = Customer::firstOrCreate(
            ['store_id' => $store->id, 'phone' => '085711223344'],
            ['name' => 'Siti Aminah', 'address' => 'Surabaya']
        );
        $device2 = CustomerDevice::firstOrCreate(
            ['store_id' => $store->id, 'customer_id' => $customer2->id, 'model' => 'Galaxy S23'],
            ['brand' => 'Samsung', 'color' => 'Phantom Black']
        );

        $customer3 = Customer::firstOrCreate(
            ['store_id' => $store->id, 'phone' => '082198765432'],
            ['name' => 'Rahmat Hidayat', 'address' => 'Batu']
        );
        $device3 = CustomerDevice::firstOrCreate(
            ['store_id' => $store->id, 'customer_id' => $customer3->id, 'model' => 'Redmi Note 12'],
            ['brand' => 'Xiaomi', 'color' => 'Ice Blue']
        );

        // 8. Service Orders
        $token1 = 'demo111111111111111111111111111111111111111111111111111111111111';
        $token2 = 'demo222222222222222222222222222222222222222222222222222222222222';
        $token3 = 'demo333333333333333333333333333333333333333333333333333333333333';

        $order1 = ServiceOrder::firstOrCreate(
            ['store_id' => $store->id, 'order_number' => 'ORD-202611-0001'],
            [
                'customer_id' => $customer1->id,
                'customer_device_id' => $device1->id,
                'technician_id' => $tech1->id,
                'created_by' => $owner->id,
                'status' => 'in_progress',
                'tracking_token' => $token1,
                'complaints' => 'Layar pecah & blank hitam setelah terjatuh, touch tidak merespon.',
                'physical_condition' => ['Lecet bezel samping', 'Kaca depan retak seribu'],
                'accessories_included' => 'Unit HP + Casing silikon',
                'estimated_cost' => 1850000,
                'total_cost' => 1850000,
                'down_payment' => 500000,
                'paid_amount' => 500000,
                'payment_status' => 'partially_paid',
                'estimated_completion_at' => now()->addDays(1),
            ]
        );

        ServiceTrackingToken::firstOrCreate(
            ['store_id' => $store->id, 'service_order_id' => $order1->id],
            [
                'token_hash' => hash('sha256', $token1),
                'expires_at' => now()->addDays(90),
            ]
        );

        $order2 = ServiceOrder::firstOrCreate(
            ['store_id' => $store->id, 'order_number' => 'ORD-202611-0002'],
            [
                'customer_id' => $customer2->id,
                'customer_device_id' => $device2->id,
                'technician_id' => $tech2->id,
                'created_by' => $owner->id,
                'status' => 'ready_for_pickup',
                'tracking_token' => $token2,
                'complaints' => 'Port charger kendor dan tidak bisa fast charging.',
                'physical_condition' => ['Mulus pemakaian normal'],
                'accessories_included' => 'Unit HP saja',
                'estimated_cost' => 250000,
                'total_cost' => 250000,
                'down_payment' => 250000,
                'paid_amount' => 250000,
                'payment_status' => 'paid',
                'estimated_completion_at' => now()->subHours(2),
            ]
        );

        ServiceTrackingToken::firstOrCreate(
            ['store_id' => $store->id, 'service_order_id' => $order2->id],
            [
                'token_hash' => hash('sha256', $token2),
                'expires_at' => now()->addDays(90),
            ]
        );

        $order3 = ServiceOrder::firstOrCreate(
            ['store_id' => $store->id, 'order_number' => 'ORD-202611-0003'],
            [
                'customer_id' => $customer3->id,
                'customer_device_id' => $device3->id,
                'technician_id' => $tech1->id,
                'created_by' => $owner->id,
                'status' => 'pending',
                'tracking_token' => $token3,
                'complaints' => 'Baterai cepat drop dan ponsel terasa panas berlebih.',
                'physical_condition' => ['Backdoor sedikit terangkat akibat baterai kembung'],
                'accessories_included' => 'Unit HP + Dusbox',
                'estimated_cost' => 320000,
                'total_cost' => 320000,
                'down_payment' => 0,
                'paid_amount' => 0,
                'payment_status' => 'unpaid',
                'estimated_completion_at' => now()->addDays(2),
            ]
        );

        ServiceTrackingToken::firstOrCreate(
            ['store_id' => $store->id, 'service_order_id' => $order3->id],
            [
                'token_hash' => hash('sha256', $token3),
                'expires_at' => now()->addDays(90),
            ]
        );

        // 9. Initial Payments & Cash Records
        Payment::firstOrCreate(
            ['store_id' => $store->id, 'payment_number' => 'PAY-202611-0001'],
            [
                'service_order_id' => $order1->id,
                'customer_id' => $customer1->id,
                'user_id' => $owner->id,
                'amount' => 500000,
                'type' => 'dp',
                'payment_method' => 'cash',
                'status' => 'success',
                'notes' => 'Uang muka pengerjaan layar iPhone 13 Pro',
            ]
        );

        Payment::firstOrCreate(
            ['store_id' => $store->id, 'payment_number' => 'PAY-202611-0002'],
            [
                'service_order_id' => $order2->id,
                'customer_id' => $customer2->id,
                'user_id' => $owner->id,
                'amount' => 250000,
                'type' => 'full_payment',
                'payment_method' => 'qris',
                'status' => 'success',
                'notes' => 'Pelunasan servis port cas Galaxy S23',
            ]
        );
    }
}
