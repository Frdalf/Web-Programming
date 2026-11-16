<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Member;

class MemberSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Member::create(['name' => 'Alip Ganteng Banget Ya Ampun']);
        Member::create(['name' => 'Another Member']);
    }
}
