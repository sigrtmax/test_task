<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Customer;
use Illuminate\Database\Seeder;

class CustomerSeeder extends Seeder
{
    private array $customers = [
        ['name' => 'Ivan Petrov', 'email' => 'ivan.petrov@example.com', 'phone' => '+79001234567'],
        ['name' => 'Anna Sidorova', 'email' => 'anna.sidorova@example.com', 'phone' => '+79007654321'],
        ['name' => 'Dmitry Volkov', 'email' => 'dmitry.volkov@example.com', 'phone' => '+79009876543'],
        ['name' => 'Elena Kozlova', 'email' => 'elena.kozlova@example.com', 'phone' => null],
        ['name' => 'Alexey Morozov', 'email' => 'alexey.morozov@example.com', 'phone' => '+79001112233'],
        ['name' => 'Natalia Novikova', 'email' => 'natalia.novikova@example.com', 'phone' => '+79004445566'],
        ['name' => 'Sergey Fedorov', 'email' => 'sergey.fedorov@example.com', 'phone' => null],
        ['name' => 'Maria Sokolova', 'email' => 'maria.sokolova@example.com', 'phone' => '+79007778899'],
        ['name' => 'Pavel Andreev', 'email' => 'pavel.andreev@example.com', 'phone' => '+79000001122'],
        ['name' => 'Olga Lebedeva', 'email' => 'olga.lebedeva@example.com', 'phone' => '+79003334455'],
    ];

    public function run(): void
    {
        foreach ($this->customers as $customer) {
            Customer::updateOrCreate(['email' => $customer['email']], $customer);
        }

        $this->command->info('Customers seeded: ' . count($this->customers) . ' records.');
    }
}
