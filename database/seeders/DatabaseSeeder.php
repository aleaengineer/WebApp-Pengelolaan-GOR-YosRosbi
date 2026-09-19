<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Lapangan;
use App\Models\HargaSewa;
use App\Models\PaketMember;
use App\Models\Setting;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Settings
        Setting::set('jam_operasional_mulai', '08:00');
        Setting::set('jam_operasional_selesai', '00:00');
        Setting::set('buffer_menit', '30');
        Setting::set('buffer_harian_menit', '30');
        Setting::set('harga_per_jam', '50000');
        Setting::set('harga_harian', '1500000');
        Setting::set('rekening', 'BCA 1234567890 a.n. GOR Yos Rosbi');
        Setting::set('kontak_wa', '62812xxxxxxx');
        Setting::set('nama_gor', 'GOR Yos Rosbi');

        // Lapangan
        $lapangan = Lapangan::firstOrCreate(['nama' => 'Lapangan Utama Yos Rosbi'], [
            'deskripsi' => 'Lapangan serbaguna untuk Badminton, Voly, Basket, dan Event Indoor. Lantai vinyl, pencahayaan LED, tribun.',
            'jenis' => ['badminton','voly','basket','event'],
            'status' => 'active'
        ]);

        // Harga
        HargaSewa::firstOrCreate(['lapangan_id' => $lapangan->id, 'tipe' => 'per_jam'], [
            'hari' => 'semua',
            'jam_mulai' => '08:00',
            'jam_selesai' => '00:00',
            'harga' => 50000,
            'harga_member' => 40000,
        ]);
        HargaSewa::firstOrCreate(['lapangan_id' => $lapangan->id, 'tipe' => 'harian'], [
            'hari' => 'semua',
            'harga' => 1500000,
            'harga_member' => 1300000,
        ]);

        // Paket Member
        PaketMember::firstOrCreate(['slug' => 'hemat-10x'], [
            'nama' => 'Paket Hemat 10x',
            'kuota_jam' => 10,
            'durasi_hari' => 30,
            'harga' => 350000,
            'deskripsi' => '10 jam dalam 30 hari, hemat 30%',
            'is_active' => true
        ]);
        PaketMember::firstOrCreate(['slug' => 'bulanan-unlimited'], [
            'nama' => 'Paket Bulanan Unlimited',
            'kuota_jam' => 60,
            'durasi_hari' => 30,
            'harga' => 800000,
            'deskripsi' => '60 jam / 30 hari, puas main tiap hari',
            'is_active' => true
        ]);
        PaketMember::firstOrCreate(['slug' => 'mingguan-5x'], [
            'nama' => 'Paket Mingguan 5x',
            'kuota_jam' => 5,
            'durasi_hari' => 7,
            'harga' => 200000,
            'deskripsi' => '5 jam dalam 7 hari',
            'is_active' => true
        ]);

        // Users
        User::firstOrCreate(['email' => 'admin@goryosrosbi.test'], [
            'name' => 'Admin GOR',
            'password' => Hash::make('password'),
            'role' => 'admin',
            'phone' => '081234567890',
            'email_verified_at' => now(),
        ]);
        User::firstOrCreate(['email' => 'operator@goryosrosbi.test'], [
            'name' => 'Operator GOR',
            'password' => Hash::make('password'),
            'role' => 'operator',
            'phone' => '081234567891',
            'email_verified_at' => now(),
        ]);
        User::firstOrCreate(['email' => 'customer@test.com'], [
            'name' => 'Customer Test',
            'password' => Hash::make('password'),
            'role' => 'customer',
            'phone' => '081234567892',
            'email_verified_at' => now(),
        ]);

        // Coupons - percent only, per_jam, 1x/user
        \App\Models\Coupon::firstOrCreate(['code' => 'YOS10'], [
            'name' => 'Diskon 10% Per Jam',
            'type' => 'percent',
            'value' => 10,
            'max_discount' => 15000,
            'min_amount' => 0,
            'quota' => 100,
            'per_user_limit' => 1,
            'tipe_sewa' => 'per_jam',
            'is_active' => true,
            'expired_at' => now()->addDays(30),
            'created_by' => User::where('email','admin@goryosrosbi.test')->value('id'),
        ]);
        \App\Models\Coupon::firstOrCreate(['code' => 'HEMAT20'], [
            'name' => 'Hemat 20% Per Jam',
            'type' => 'percent',
            'value' => 20,
            'max_discount' => 20000,
            'min_amount' => 50000,
            'quota' => 50,
            'per_user_limit' => 1,
            'tipe_sewa' => 'per_jam',
            'is_active' => true,
            'expired_at' => now()->addDays(14),
            'created_by' => User::where('email','admin@goryosrosbi.test')->value('id'),
        ]);
        \App\Models\Coupon::firstOrCreate(['code' => 'EXPIRED'], [
            'name' => 'Test Expired',
            'type' => 'percent',
            'value' => 50,
            'max_discount' => 50000,
            'min_amount' => 0,
            'quota' => 10,
            'per_user_limit' => 1,
            'tipe_sewa' => 'per_jam',
            'is_active' => true,
            'expired_at' => now()->subDay(),
            'created_by' => User::where('email','admin@goryosrosbi.test')->value('id'),
        ]);

        // Demo bookings untuk test jeda 30 menit (idempotent)
        if (\App\Models\Booking::count() === 0) {
            $customer = User::where('email','customer@test.com')->first();
            $b = \App\Models\Booking::create([
                'user_id' => $customer->id,
                'lapangan_id' => $lapangan->id,
                'tanggal' => now()->format('Y-m-d'),
                'jam_mulai' => '08:00',
                'jam_selesai' => '09:00',
                'jenis_kegiatan' => 'badminton',
                'tipe_sewa' => 'per_jam',
                'durasi_jam' => 1,
                'total_harga' => 50000,
                'status' => 'paid',
            ]);
            \App\Models\Payment::create(['booking_id'=>$b->id,'metode'=>'transfer','amount'=>50000,'status'=>'paid','paid_at'=>now()]);
        }
    }
}
