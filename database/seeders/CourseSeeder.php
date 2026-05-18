<?php

namespace Database\Seeders;

use App\Models\Course;
use Illuminate\Database\Seeder;

class CourseSeeder extends Seeder
{
    public function run(): void
    {
        $courses = [
            ['code' => 'BSIT', 'name' => 'Bachelor of Science in Information Technology'],
            ['code' => 'BSIS', 'name' => 'Bachelor of Science in Information Systems'],
            ['code' => 'BSED', 'name' => 'Bachelor of Secondary Education'],
            ['code' => 'BEED', 'name' => 'Bachelor of Elementary Education'],
            ['code' => 'BSHM', 'name' => 'Bachelor of Science in Hospitality Management'],
            ['code' => 'BSTM', 'name' => 'Bachelor of Science in Tourism Management'],
        ];

        foreach ($courses as $data) {
            Course::updateOrCreate(
                ['code' => $data['code']],
                ['name' => $data['name'], 'description' => null]
            );
        }

        $this->command->info('Seeded '.count($courses).' courses.');
    }
}
