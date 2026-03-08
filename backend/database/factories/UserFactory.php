<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class UserFactory extends Factory
{
    protected static ?string $password;

    public function definition(): array
    {
        $name = fake()->userName();
        return [
            'username' => Str::limit($name, 50),
            'email' => fake()->unique()->safeEmail(),
            'password_hash' => static::$password ??= Hash::make('password'),
            'display_name' => fake()->name(),
            'timezone' => 'Asia/Taipei',
            'morning_reminder_time' => '08:00:00',
            'evening_reminder_time' => '20:00:00',
            'is_morning_reminder_enabled' => true,
            'is_evening_reminder_enabled' => true,
            'last_login' => null,
            'is_active' => true,
        ];
    }
}
