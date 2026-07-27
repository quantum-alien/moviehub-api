<?php

namespace Database\Seeders;

use App\Models\Genre;
use App\Models\Movie;
use App\Models\Review;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::factory()->admin()->create([
            'name' => 'Admin',
            'email' => 'admin@moviehub.test',
        ]);

        $moderator = User::factory()->moderator()->create([
            'name' => 'Moderator',
            'email' => 'moderator@moviehub.test',
        ]);

        $users = User::factory()->count(20)->create();

        $genres = collect([
            'Драма', 'Комедия', 'Боевик', 'Фантастика', 'Триллер',
            'Ужасы', 'Мелодрама', 'Детектив', 'Анимация', 'Документальный',
        ])->map(fn ($name) => Genre::factory()->create(['name' => $name]));

        Movie::factory()
            ->count(50)
            ->create(['added_by' => $admin->id])
            ->each(function (Movie $movie) use ($genres, $users) {
                $movie->genres()->attach(
                    $genres->random(random_int(1, 3))->pluck('id')->toArray()
                );

                $reviewers = $users->random(random_int(0, 10));

                foreach ($reviewers as $user) {
                    Review::factory()->create([
                        'movie_id' => $movie->id,
                        'user_id' => $user->id,
                    ]);
                }

                $stats = $movie->reviews()->selectRaw('AVG(rating) as avg_rating, COUNT(*) as cnt')->first();

                $movie->update([
                    'avg_rating' => round((float) $stats->avg_rating, 2),
                    'reviews_count' => (int) $stats->cnt,
                ]);
            });

        $this->command->info('База данных успешно наполнена тестовыми данными.');
        $this->command->info('Admin: admin@moviehub.test / password');
        $this->command->info('Moderator: moderator@moviehub.test / password');
    }
}
