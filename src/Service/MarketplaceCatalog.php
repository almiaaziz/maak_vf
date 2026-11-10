<?php

namespace App\Service;

final class MarketplaceCatalog
{
    public function all(): array
    {
        $curriculum = static fn (string $prefix): array => [
            ['title' => 'Getting started', 'lessons' => [
                ['title' => 'Welcome and course overview', 'type' => 'Video', 'duration' => '06:20'],
                ['title' => 'The foundations', 'type' => 'Video', 'duration' => '12:45'],
                ['title' => 'Your first practice sheet', 'type' => 'PDF', 'duration' => '5 min'],
            ]],
            ['title' => 'Build your understanding', 'lessons' => [
                ['title' => 'Core concepts explained', 'type' => 'Video', 'duration' => '18:30'],
                ['title' => 'Worked examples', 'type' => 'Text', 'duration' => '10 min'],
                ['title' => 'Check your knowledge', 'type' => 'Quiz', 'duration' => '8 min'],
            ]],
            ['title' => 'Ready for exams', 'lessons' => [
                ['title' => 'Exam-style exercises', 'type' => 'Video', 'duration' => '15:10'],
                ['title' => 'Revision workbook', 'type' => 'Document', 'duration' => '12 min'],
            ]],
        ];

        $items = [
            ['id'=>'algebra-essentials','title'=>'Algebra Essentials: From Basics to Confidence','subtitle'=>'Make equations feel simple with clear explanations, guided exercises and exam-ready practice.','subject'=>'Mathematics','level'=>'Middle school','teacher'=>'Amira Ben Salem','teacherId'=>'amira','price'=>25,'oldPrice'=>35,'rating'=>4.9,'reviews'=>128,'duration'=>'4h 20m','image'=>'course-math.jpg','objectives'=>['Understand algebraic equations step by step','Solve problems with confidence','Prepare for school exams with guided practice']],
            ['id'=>'physics-made-simple','title'=>'Physics Made Simple: Motion & Energy','subtitle'=>'Explore the ideas behind motion, forces and energy through approachable lessons.','subject'=>'Physics','level'=>'High school','teacher'=>'Yassine Trabelsi','teacherId'=>'yassine','price'=>35,'rating'=>4.8,'reviews'=>96,'duration'=>'5h 10m','image'=>'course-science.jpg','objectives'=>['Explain forces and motion','Apply key physics formulas','Practice with exam-style questions']],
            ['id'=>'english-everyday','title'=>'Everyday English for Young Learners','subtitle'=>'Build useful vocabulary and speaking confidence one lesson at a time.','subject'=>'English','level'=>'Middle school','teacher'=>'Inès Mansour','teacherId'=>'ines','price'=>20,'oldPrice'=>28,'rating'=>4.9,'reviews'=>84,'duration'=>'3h 45m','image'=>'course-english.jpg','objectives'=>['Use everyday English naturally','Grow vocabulary and grammar skills','Feel ready for class conversations']],
            ['id'=>'chemistry-foundations','title'=>'Chemistry Foundations: Matter & Reactions','subtitle'=>'Understand atoms, elements and reactions with visual examples and practice.','subject'=>'Chemistry','level'=>'High school','teacher'=>'Yassine Trabelsi','teacherId'=>'yassine','price'=>30,'rating'=>4.7,'reviews'=>71,'duration'=>'4h 05m','image'=>'course-science.jpg','objectives'=>['Understand elements and compounds','Balance simple reactions','Practice essential chemistry problems']],
            ['id'=>'geometry-workshop','title'=>'Geometry Workshop: Shapes & Proofs','subtitle'=>'A visual route to geometry concepts, formulas and problem solving.','subject'=>'Mathematics','level'=>'Primary','teacher'=>'Amira Ben Salem','teacherId'=>'amira','price'=>15,'rating'=>4.8,'reviews'=>63,'duration'=>'2h 50m','image'=>'course-math.jpg','objectives'=>['Recognize geometric relationships','Use area and perimeter formulas','Solve visual problems']],
            ['id'=>'french-confident','title'=>'French Grammar with Confidence','subtitle'=>'Clear French grammar lessons and practical writing exercises.','subject'=>'French','level'=>'Middle school','teacher'=>'Inès Mansour','teacherId'=>'ines','price'=>22,'rating'=>4.7,'reviews'=>52,'duration'=>'3h 15m','image'=>'course-english.jpg','objectives'=>['Master essential grammar','Write clearer sentences','Practice for assessments']],
        ];

        foreach ($items as &$item) {
            $item['sections'] = $curriculum($item['id']);
            $item['lessonCount'] = array_sum(array_map(static fn ($section) => count($section['lessons']), $item['sections']));
        }
        return $items;
    }

    public function teachers(): array
    {
        return [
            'amira'=>['id'=>'amira','name'=>'Amira Ben Salem','initials'=>'AB','university'=>'University of Tunis El Manar','field'=>'Mathematics','level'=>"Master’s student",'bio'=>'Amira turns challenging maths topics into small, achievable steps. She loves helping learners discover that they can solve more than they think.','rating'=>4.9],
            'yassine'=>['id'=>'yassine','name'=>'Yassine Trabelsi','initials'=>'YT','university'=>'National Engineering School of Tunis','field'=>'Physics & Chemistry','level'=>'Engineering student','bio'=>'Yassine connects science to everyday life through clear explanations and practical examples.','rating'=>4.8],
            'ines'=>['id'=>'ines','name'=>'Inès Mansour','initials'=>'IM','university'=>'University of Carthage','field'=>'Languages','level'=>"Master’s student",'bio'=>'Inès makes language learning welcoming, practical and enjoyable for young learners.','rating'=>4.9],
        ];
    }

    public function subjects(): array
    {
        return ['Mathematics','Physics','Chemistry','Biology','English','French','Computer Science','Programming'];
    }
}
