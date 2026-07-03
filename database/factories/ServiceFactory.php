<?php

namespace Database\Factories;

use App\Models\Service;
use Illuminate\Database\Eloquent\Factories\Factory;

class ServiceFactory extends Factory
{
    protected $model = Service::class;

    public function definition(): array
    {
        $jasa = fake()->randomElement([200000, 500000, 800000, 1000000]);
        $pengeluaran = fake()->randomElement([50000, 100000, 200000, 300000]);

        return [
            'service_number' => 'SRV-TEST-' . fake()->unique()->randomNumber(5),
            'tanggal' => fake()->dateTimeThisMonth(),
            'customer_id' => \App\Models\Customer::factory(),
            'type_hp' => fake()->randomElement(['iPhone 12', 'Samsung S24', 'Xiaomi Note 13']),
            'kerusakan' => json_encode([fake()->randomElement(['LCD Retak', 'Batre Boros', 'Touch ID Rusak'])]),
            'jasa' => $jasa,
            'pengeluaran' => $pengeluaran,
            'keuntungan' => $jasa - $pengeluaran,
            'status' => 'menunggu',
        ];
    }
}
