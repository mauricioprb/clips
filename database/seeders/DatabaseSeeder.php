<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\Priority;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::factory()->admin()->create([
            'name' => 'Bolsista de Teste',
            'email' => 'bolsista@example.com',
        ]);

        $user->weeklySlots()->create([
            'weekday' => 2,
            'start_minute' => 840,
            'end_minute' => 960,
            'description' => 'Aula de Estrutura da Matéria',
        ]);

        $user->defaultActivities()->createMany([
            ['description' => 'Pesquisa bibliográfica', 'priority' => Priority::High],
            ['description' => 'Escrita da dissertação', 'priority' => Priority::Medium],
            ['description' => 'Organização do laboratório', 'priority' => Priority::Low],
        ]);
    }
}
