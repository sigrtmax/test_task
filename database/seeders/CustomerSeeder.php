<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Customer;
use Illuminate\Database\Seeder;

class CustomerSeeder extends Seeder
{
    private array $customers = [
        ['name' => 'Иван Петров', 'email' => 'ivan.petrov@example.com', 'phone' => '+79001234567'],
        ['name' => 'Анна Сидорова', 'email' => 'anna.sidorova@example.com', 'phone' => '+79007654321'],
        ['name' => 'Дмитрий Волков', 'email' => 'dmitry.volkov@example.com', 'phone' => '+79009876543'],
        ['name' => 'Елена Козлова', 'email' => 'elena.kozlova@example.com', 'phone' => null],
        ['name' => 'Алексей Морозов', 'email' => 'alexey.morozov@example.com', 'phone' => '+79001112233'],
        ['name' => 'Наталья Новикова', 'email' => 'natalia.novikova@example.com', 'phone' => '+79004445566'],
        ['name' => 'Сергей Фёдоров', 'email' => 'sergey.fedorov@example.com', 'phone' => null],
        ['name' => 'Мария Соколова', 'email' => 'maria.sokolova@example.com', 'phone' => '+79007778899'],
        ['name' => 'Павел Андреев', 'email' => 'pavel.andreev@example.com', 'phone' => '+79000001122'],
        ['name' => 'Ольга Лебедева', 'email' => 'olga.lebedeva@example.com', 'phone' => '+79003334455'],
    ];

    public function run(): void
    {
        foreach ($this->customers as $customer) {
            Customer::updateOrCreate(['email' => $customer['email']], $customer);
        }

        $this->command->info('Клиенты загружены: ' . count($this->customers) . ' записей.');
    }
}
