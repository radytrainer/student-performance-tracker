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
    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $first = fake()->firstName();
        $last = fake()->lastName();
        $email = fake()->unique()->safeEmail();
        return [
            'first_name' => $first,
            'last_name' => $last,
            'email' => $email,
            'username' => $email,
            'password_hash' => static::$password ??= Hash::make('password'),
            'role' => 'student',
            'is_active' => true,
        ];
    }

    public function configure()
    {
        return $this->afterCreating(function (\App\Models\User $user) {
            try {
                if ($user->role === 'student') {
                    \App\Models\Student::create([
                        'user_id' => $user->id,
                        'student_code' => 'STU' . date('Y') . str_pad((string) random_int(1, 9999), 4, '0', STR_PAD_LEFT),
                        'enrollment_date' => now(),
                        'current_class_id' => null,
                    ]);
                }
            } catch (\Throwable $e) {
                // swallow to avoid factory hard failures in tests
            }
        });
    }

    /**
     * Indicate that the model's email address should be unverified.
     */
    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }
}
