<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Ticket;
use App\Models\User;
use App\TicketPriority;
use App\TicketStatus;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call(CategorySeeder::class);

        $owner = User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

        $technician = User::factory()->technician()->create([
            'name' => 'Budi Teknisi',
            'email' => 'technician@example.com',
        ]);

        $colleague = User::factory()->create([
            'name' => 'Sari Karyawan',
            'email' => 'sari@example.com',
        ]);

        $wifi = Category::where('slug', 'wifi')->first();
        $cctv = Category::where('slug', 'cctv')->first();
        $printer = Category::where('slug', 'printer')->first();

        // Ticket milik Test User (login: test@example.com) — berbagai status.
        $ticket1 = Ticket::factory()->create([
            'user_id' => $owner->id,
            'category_id' => $wifi?->id,
            'title' => 'Wifi lab 3 mati total sejak pagi',
            'description' => "Access point di lab 3 tidak menyala sejak pagi, lampu indicator off.\nSudah dicoba restart listrik, tetap mati.",
            'priority' => TicketPriority::High,
            'status' => TicketStatus::InProgress,
            'assigned_to' => $technician->id,
        ]);

        $ticket2 = Ticket::factory()->create([
            'user_id' => $owner->id,
            'category_id' => $printer?->id,
            'title' => 'Printer ruang TU tidak bisa mencetak',
            'description' => 'Printer selalu error paper jam padahal kertas sudah disusun rapi dan tidak ada yang tersangkut.',
            'priority' => TicketPriority::Medium,
            'status' => TicketStatus::Open,
        ]);

        $ticket3 = Ticket::factory()->create([
            'user_id' => $owner->id,
            'category_id' => $wifi?->id,
            'title' => 'Sinyal wifi asrama lemah di lantai 2',
            'description' => 'Sinyal wifi sangat lemah di kamar asrama lantai 2 blok B, sering putus-putus.',
            'priority' => TicketPriority::Low,
            'status' => TicketStatus::Resolved,
            'assigned_to' => $technician->id,
        ]);

        // Ticket milik user lain — tidak boleh terlihat oleh Test User.
        Ticket::factory()->create([
            'user_id' => $colleague->id,
            'category_id' => $cctv?->id,
            'title' => 'CCTV gerbang utama blur sejak kemarin',
            'description' => 'Rekaman CCTV gerbang utama terlihat blur, kemungkinan lensa berembun atau kabel longgar.',
            'priority' => TicketPriority::High,
            'status' => TicketStatus::Open,
        ]);

        // Komentar: publik + internal (teknisi).
        $owner->comments()->create([
            'ticket_id' => $ticket1->id,
            'body' => 'Kabel listrik di lab 3 sempat dicabut pas cleaning tadi pagi, dicek lagi ya mas.',
            'is_internal' => false,
        ]);

        $technician->comments()->create([
            'ticket_id' => $ticket1->id,
            'body' => 'Indikator mati total → kemungkinan adaptor/POE switch mati. Cek swap alat dulu.',
            'is_internal' => true,
        ]);

        $technician->comments()->create([
            'ticket_id' => $ticket1->id,
            'body' => 'Adaptor diganti, access point nyala lagi. Menunggu konfirmasi dari pemohon sebelum di-resolve.',
            'is_internal' => false,
        ]);

        $technician->comments()->create([
            'ticket_id' => $ticket3->id,
            'body' => 'Tambah 1 access point di blok B lantai 2, sudah diusulkan ke bagian supply.',
            'is_internal' => true,
        ]);
    }
}
