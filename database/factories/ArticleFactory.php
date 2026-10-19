<?php

namespace Database\Factories;

use App\Models\Article;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Article>
 */
class ArticleFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $title = fake()->sentence(5);

        return [
            'title' => $title,
            'slug' => Str::slug($title).'-'.fake()->unique()->numberBetween(1, 9999),
            'category' => 'Persediaan',
            'excerpt' => fake()->sentence(),
            'body' => fake()->paragraphs(3, true),
            'status' => 'published',
            'published_at' => now()->subDay(),
        ];
    }
}
