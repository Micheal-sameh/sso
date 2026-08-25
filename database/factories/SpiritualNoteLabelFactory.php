<?php

namespace Database\Factories;

use App\Models\SpiritualNoteLabel;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SpiritualNoteLabel>
 */
class SpiritualNoteLabelFactory extends Factory
{
    protected $model = SpiritualNoteLabel::class;

    public function definition(): array
    {
        return [
            'name' => fake()->unique()->words(2, true),
            'sort_order' => 0,
            'is_active' => true,
        ];
    }
}
