<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Model>
 */
class TransactionFactory extends Factory
{
    const STATUSES = ['settled', 'error', 'authorized', 'refused'];
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
        'psp_reference' => fake()->iban('UA'),
        'merchant_reference' => strtoupper(fake()->bothify('??######')),
        'amount' => fake()->randomFloat(2, 71, 343),
        'payment_method' => fake()->creditCardType(),
        'status' => array_rand(self::STATUSES),
        'risk_score' => fake()->numberBetween(0, 50),
        ];
    }
}
