<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\BankAccount;

class BankAccountSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        BankAccount::create([
            'bank_name' => 'BNI',
            'account_number' => '1234567890',
            'account_holder' => 'PT. Ecommerce Indonesia',
            'is_active' => true,
        ]);

        BankAccount::create([
            'bank_name' => 'BCA',
            'account_number' => '0987654321',
            'account_holder' => 'PT. Ecommerce Indonesia',
            'is_active' => true,
        ]);
    }
}

