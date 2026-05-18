<?php

namespace Database\Seeders;

use App\Models\Course;
use Illuminate\Database\Seeder;

class CourseSeeder extends Seeder
{
    public function run(): void
    {
        $courses = [

            // ── General Education ──────────────────────────────────────────
            ['code' => 'GE 001',  'name' => 'Understanding the Self'],
            ['code' => 'GE 002',  'name' => 'Readings in Philippine History'],
            ['code' => 'GE 003',  'name' => 'The Contemporary World'],
            ['code' => 'GE 004',  'name' => 'Mathematics in the Modern World'],
            ['code' => 'GE 005',  'name' => 'Purposive Communication'],
            ['code' => 'GE 006',  'name' => 'Art Appreciation'],
            ['code' => 'GE 007',  'name' => 'Science, Technology and Society'],
            ['code' => 'GE 008',  'name' => 'Ethics'],
            ['code' => 'GE 009',  'name' => 'Life and Works of Rizal'],
            ['code' => 'GE 010',  'name' => 'Filipino sa Piling Larangan'],
            ['code' => 'GE 011',  'name' => 'Panitikang Panlipunan'],
            ['code' => 'PE 001',  'name' => 'Physical Education 1 (Movement Enhancement)'],
            ['code' => 'PE 002',  'name' => 'Physical Education 2 (Fitness Exercise)'],
            ['code' => 'PE 003',  'name' => 'Physical Education 3 (Team Sports)'],
            ['code' => 'PE 004',  'name' => 'Physical Education 4 (Individual/Dual Sports)'],
            ['code' => 'NSTP 1',  'name' => 'National Service Training Program 1'],
            ['code' => 'NSTP 2',  'name' => 'National Service Training Program 2'],

            // ── Information Technology (IT) ────────────────────────────────
            ['code' => 'IT 101',  'name' => 'Introduction to Computing'],
            ['code' => 'IT 102',  'name' => 'Computer Programming 1 (Python)'],
            ['code' => 'IT 103',  'name' => 'Computer Programming 2 (Java)'],
            ['code' => 'IT 104',  'name' => 'Data Structures and Algorithms'],
            ['code' => 'IT 105',  'name' => 'Discrete Mathematics'],
            ['code' => 'IT 106',  'name' => 'Object-Oriented Programming'],
            ['code' => 'IT 107',  'name' => 'Web Development 1 (HTML/CSS/JS)'],
            ['code' => 'IT 108',  'name' => 'Web Development 2 (Frameworks)'],
            ['code' => 'IT 109',  'name' => 'Database Management Systems'],
            ['code' => 'IT 110',  'name' => 'Operating Systems'],
            ['code' => 'IT 111',  'name' => 'Computer Networks'],
            ['code' => 'IT 112',  'name' => 'Information Assurance and Security'],
            ['code' => 'IT 113',  'name' => 'Systems Analysis and Design'],
            ['code' => 'IT 114',  'name' => 'Software Engineering'],
            ['code' => 'IT 115',  'name' => 'Human-Computer Interaction'],
            ['code' => 'IT 116',  'name' => 'Mobile Application Development'],
            ['code' => 'IT 117',  'name' => 'Capstone Project 1'],
            ['code' => 'IT 118',  'name' => 'Capstone Project 2'],
            ['code' => 'IT 119',  'name' => 'Practicum / On-the-Job Training'],
            ['code' => 'IT 120',  'name' => 'Cloud Computing'],
            ['code' => 'IT 121',  'name' => 'Artificial Intelligence'],
            ['code' => 'IT 122',  'name' => 'Internet of Things'],
            ['code' => 'IT 123',  'name' => 'Technopreneurship'],

            // ── Education (EDUC) ───────────────────────────────────────────
            ['code' => 'EDUC 101', 'name' => 'Child and Adolescent Development'],
            ['code' => 'EDUC 102', 'name' => 'The Teaching Profession'],
            ['code' => 'EDUC 103', 'name' => 'The Social Dimensions of Education'],
            ['code' => 'EDUC 104', 'name' => 'Facilitating Learner-Centered Teaching'],
            ['code' => 'EDUC 105', 'name' => 'Assessment in Learning 1'],
            ['code' => 'EDUC 106', 'name' => 'Assessment in Learning 2'],
            ['code' => 'EDUC 107', 'name' => 'Curriculum and Instruction'],
            ['code' => 'EDUC 108', 'name' => 'Technology for Teaching and Learning 1'],
            ['code' => 'EDUC 109', 'name' => 'Technology for Teaching and Learning 2'],
            ['code' => 'EDUC 110', 'name' => 'Building and Enhancing Literacy Skills'],
            ['code' => 'EDUC 111', 'name' => 'Teaching Science in Elementary Grades'],
            ['code' => 'EDUC 112', 'name' => 'Teaching Mathematics in Elementary Grades'],
            ['code' => 'EDUC 113', 'name' => 'Teaching Language and Literacy'],
            ['code' => 'EDUC 114', 'name' => 'Principles of Teaching 1'],
            ['code' => 'EDUC 115', 'name' => 'Principles of Teaching 2'],
            ['code' => 'EDUC 116', 'name' => 'Field Study 1 (Observation and Participation)'],
            ['code' => 'EDUC 117', 'name' => 'Field Study 2 (Participation and Teaching)'],
            ['code' => 'EDUC 118', 'name' => 'Practice Teaching (Student Teaching)'],
            ['code' => 'EDUC 119', 'name' => 'Special Topics in Education'],
            ['code' => 'EDUC 120', 'name' => 'Inclusive Education'],

            // ── SHTM (School of Hospitality and Tourism Management) ────────
            ['code' => 'SHTM 101', 'name' => 'Introduction to Hospitality Management'],
            ['code' => 'SHTM 102', 'name' => 'Introduction to Tourism Management'],
            ['code' => 'SHTM 103', 'name' => 'Food and Beverage Service'],
            ['code' => 'SHTM 104', 'name' => 'Front Office Operations'],
            ['code' => 'SHTM 105', 'name' => 'Housekeeping Operations'],
            ['code' => 'SHTM 106', 'name' => 'Food Safety and Sanitation'],
            ['code' => 'SHTM 107', 'name' => 'Culinary Arts 1 (Basic Cooking)'],
            ['code' => 'SHTM 108', 'name' => 'Culinary Arts 2 (Advanced Cooking)'],
            ['code' => 'SHTM 109', 'name' => 'Baking and Pastry Arts'],
            ['code' => 'SHTM 110', 'name' => 'Bar and Beverage Management'],
            ['code' => 'SHTM 111', 'name' => 'Tourism Geography'],
            ['code' => 'SHTM 112', 'name' => 'Tour Guiding and Operations'],
            ['code' => 'SHTM 113', 'name' => 'Travel Agency and Tour Operations'],
            ['code' => 'SHTM 114', 'name' => 'Hotel and Restaurant Management'],
            ['code' => 'SHTM 115', 'name' => 'Events Management'],
            ['code' => 'SHTM 116', 'name' => 'Hospitality Marketing'],
            ['code' => 'SHTM 117', 'name' => 'Strategic Management in Hospitality'],
            ['code' => 'SHTM 118', 'name' => 'Practicum 1 (Hospitality)'],
            ['code' => 'SHTM 119', 'name' => 'Practicum 2 (Tourism)'],
            ['code' => 'SHTM 120', 'name' => 'Research Methods in Hospitality and Tourism'],
        ];

        foreach ($courses as $data) {
            Course::updateOrCreate(
                ['code' => $data['code']],
                ['name' => $data['name'], 'description' => null]
            );
        }

        $this->command->info('Seeded ' . count($courses) . ' courses.');
    }
}
