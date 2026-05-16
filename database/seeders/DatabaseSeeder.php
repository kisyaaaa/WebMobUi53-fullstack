<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::transaction(
            function () {
                // Insert John Doe into the users table
                DB::table('users')->insert([
                    'id' => 1,
                    'first_name' => 'John',
                    'last_name' => 'Doe',
                    'username' => 'johndoe',
                    'email' => 'john.doe@example.com',
                    'password' => Hash::make('password'),
                    'created_at' => new \DateTime('2026-02-09 10:00:00'),
                    'updated_at' => new \DateTime('2026-02-09 10:00:00'),
                ]);

                // Insert Jane Doe into the users table
                DB::table('users')->insert([
                    'id' => 2,
                    'first_name' => 'Jane',
                    'last_name' => 'Doe',
                    'username' => 'janedoe',
                    'email' => 'jane.doe@example.com',
                    'password' => Hash::make('password'),
                    'created_at' => new \DateTime('2026-02-09 11:00:00'),
                    'updated_at' => new \DateTime('2026-02-09 11:00:00'),
                ]);

                // Insert some posts for John Doe
                DB::table('posts')->insert([
                    [
                        'id' => 1,
                        'user_id' => 1,
                        'title' => "John's First Post",
                        'content' => "This is the content of John's first post.",
                        'created_at' => new \DateTime('2026-02-09 12:00:00'),
                        'updated_at' => new \DateTime('2026-02-09 12:00:00'),
                    ],
                    [
                        'id' => 2,
                        'user_id' => 1,
                        'title' => null,
                        'content' => "This is the content of John's second post.",
                        'created_at' => new \DateTime('2026-02-09 12:05:00'),
                        'updated_at' => new \DateTime('2026-02-09 12:05:00'),
                    ],
                    [
                        'id' => 3,
                        'user_id' => 1,
                        'title' => null,
                        'content' => "This is the content of John's third post.",
                        'created_at' => new \DateTime('2026-02-09 12:10:00'),
                        'updated_at' => new \DateTime('2026-02-09 12:10:00'),
                    ]
                ]);

                // Insert some posts for Jane Doe
                DB::table('posts')->insert([
                    [
                        'id' => 4,
                        'user_id' => 2,
                        'title' => null,
                        'content' => "This is the content of Jane's first post.",
                        'created_at' => new \DateTime('2026-02-09 12:05:00'),
                        'updated_at' => new \DateTime('2026-02-09 12:05:00'),
                    ],
                    [
                        'id' => 5,
                        'user_id' => 2,
                        'title' => "Jane's Second Post",
                        'content' => "This is the content of Jane's second post.",
                        'created_at' => new \DateTime('2026-02-09 12:10:00'),
                        'updated_at' => new \DateTime('2026-02-09 12:10:00'),
                    ],
                    [
                        'id' => 6,
                        'user_id' => 2,
                        'title' => "Jane's Third Post",
                        'content' => "This is the content of Jane's third post.",
                        'created_at' => new \DateTime('2026-02-09 12:15:00'),
                        'updated_at' => new \DateTime('2026-02-09 12:15:00'),
                    ]
                ]);

                // Insert some likes for John's posts
                DB::table('likes')->insert([
                    [
                        'user_id' => 2,
                        'post_id' => 1,
                        'reaction' => 'like',
                        'created_at' => new \DateTime('2026-02-09 12:20:00'),
                        'updated_at' => new \DateTime('2026-02-09 12:20:00'),
                    ],
                    [
                        'user_id' => 1, // John likes his own post
                        'post_id' => 2,
                        'reaction' => 'love',
                        'created_at' => new \DateTime('2026-02-09 12:25:00'),
                        'updated_at' => new \DateTime('2026-02-09 12:25:00'),
                    ],
                ]);

                // Insert some likes for Jane's posts
                DB::table('likes')->insert([
                    [
                        'user_id' => 1,
                        'post_id' => 4,
                        'reaction' => 'like',
                        'created_at' => new \DateTime('2026-02-09 12:30:00'),
                        'updated_at' => new \DateTime('2026-02-09 12:30:00'),
                    ],
                    [
                        'user_id' => 1,
                        'post_id' => 5,
                        'reaction' => 'love',
                        'created_at' => new \DateTime('2026-02-09 12:35:00'),
                        'updated_at' => new \DateTime('2026-02-09 12:35:00'),
                    ],
                    [
                        'user_id' => 2, // Jane likes her own post
                        'post_id' => 5,
                        'reaction' => 'wow',
                        'created_at' => new \DateTime('2026-02-09 12:40:00'),
                        'updated_at' => new \DateTime('2026-02-09 12:40:00'),
                    ]
                ]);

                                // 5 sondages variés pour John Doe
                $pollsData = [
                    [
                        'id' => 1,
                        'title' => 'Tech préférences',
                        'question' => 'Quel est ton langage de programmation préféré ?',
                        'is_draft' => false,
                        'allow_multiple_choices' => false,
                        'results_public' => true,
                        'started_at' => new \DateTime('2026-05-10 09:00:00'),
                        'options' => ['JavaScript', 'Python', 'PHP', 'Rust', 'Go'],
                    ],
                    [
                        'id' => 2,
                        'title' => 'Frameworks frontend',
                        'question' => 'Quels frameworks frontend utilises-tu régulièrement ?',
                        'is_draft' => false,
                        'allow_multiple_choices' => true,
                        'results_public' => true,
                        'started_at' => new \DateTime('2026-05-12 14:00:00'),
                        'options' => ['Vue.js', 'React', 'Angular', 'Svelte', 'Solid'],
                    ],
                    [
                        'id' => 3,
                        'title' => null,
                        'question' => 'Es-tu pour le télétravail ?',
                        'is_draft' => true,
                        'allow_multiple_choices' => false,
                        'results_public' => false,
                        'options' => ['Oui, totalement', 'Hybride', 'Non, présentiel uniquement'],
                    ],
                    [
                        'id' => 4,
                        'title' => 'OS de dev (sondage terminé)',
                        'question' => 'Quel système d\'exploitation utilises-tu pour développer ?',
                        'is_draft' => false,
                        'allow_multiple_choices' => false,
                        'results_public' => true,
                        'duration' => 86400,
                        'started_at' => new \DateTime('2026-04-20 10:00:00'),
                        'ends_at' => new \DateTime('2026-04-21 10:00:00'),
                        'options' => ['Linux', 'macOS', 'Windows', 'BSD'],
                    ],
                    [
                        'id' => 5,
                        'title' => 'Soirée idéale',
                        'question' => 'Quelles activités du soir préfères-tu ?',
                        'is_draft' => true,
                        'allow_multiple_choices' => true,
                        'results_public' => false,
                        'options' => ['Lecture', 'Cinéma', 'Sport', 'Cuisine', 'Jeux vidéo', 'Musique'],
                    ],
                ];

                foreach ($pollsData as $data) {
                    DB::table('polls')->insert([
                        'id' => $data['id'],
                        'user_id' => 1,
                        'title' => $data['title'],
                        'question' => $data['question'],
                        'secret_token' => \Illuminate\Support\Str::random(32),
                        'is_draft' => $data['is_draft'],
                        'allow_multiple_choices' => $data['allow_multiple_choices'],
                        'allow_vote_change' => false,
                        'results_public' => $data['results_public'],
                        'duration' => $data['duration'] ?? null,
                        'started_at' => $data['started_at'] ?? null,
                        'ends_at' => $data['ends_at'] ?? null,
                        'created_at' => new \DateTime('2026-04-19 10:00:00'),
                        'updated_at' => new \DateTime('2026-04-19 10:00:00'),
                    ]);

                    foreach ($data['options'] as $label) {
                        DB::table('poll_options')->insert([
                            'poll_id' => $data['id'],
                            'label' => $label,
                            'created_at' => new \DateTime('2026-04-19 10:00:00'),
                            'updated_at' => new \DateTime('2026-04-19 10:00:00'),
                        ]);
                    }
                }
            }
        );
    }
}
