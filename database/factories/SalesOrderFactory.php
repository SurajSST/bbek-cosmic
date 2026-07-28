<?php

namespace Database\Factories;

use App\Models\SalesOrder;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\SalesOrder>
 */
class SalesOrderFactory extends Factory
{
    protected $model = SalesOrder::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'so_number' => 'SO-' . strtoupper(Str::random(6)),
            'so_from' => $this->faker->randomElement(['Cloud', 'Dragon', 'Cosmic']),
            'billed_from' => $this->faker->randomElement(['Cloud', 'Dragon', 'Cosmic']),
            'billed_to' => $this->faker->randomElement(['PBS', 'Prativa Plus Two', 'Prativa School', 'EGA']),
            'billed_status' => $this->faker->randomElement(['pending', 'billed', 'paid', 'cancelled']),
            'bill_no' => 'INV-' . rand(1000, 9999),
            'remarks' => $this->faker->sentence(),
            'description' => $this->faker->paragraph(),
            'created_by' => User::factory(),
        ];
    }
}
