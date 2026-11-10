<?php

namespace App\Controller;

use App\Service\MarketplaceCatalog;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class MarketplaceController extends AbstractController
{
    #[Route('/courses', name: 'app_courses', methods: ['GET'])]
    public function courses(Request $request, MarketplaceCatalog $catalog): Response
    {
        $items = $catalog->all();
        $subject = $request->query->get('subject');
        $level = $request->query->get('level');
        $query = trim((string) $request->query->get('q'));
        $price = $request->query->get('price');

        $items = array_values(array_filter($items, static function (array $course) use ($subject, $level, $query, $price): bool {
            if ($subject && $course['subject'] !== $subject) return false;
            if ($level && $course['level'] !== $level) return false;
            if ($query && stripos($course['title'].' '.$course['teacher'].' '.$course['subject'], $query) === false) return false;
            if ($price === 'free' && $course['price'] !== 0) return false;
            if ($price === 'under20' && $course['price'] > 20) return false;
            if ($price === '20to50' && ($course['price'] < 20 || $course['price'] > 50)) return false;
            if ($price === 'over50' && $course['price'] < 50) return false;
            return true;
        }));

        return $this->render('catalogue/index.html.twig', [
            'courses' => $items,
            'subjects' => $catalog->subjects(),
            'selectedSubject' => $subject,
            'selectedLevel' => $level,
            'selectedPrice' => $price,
            'searchQuery' => $query,
        ]);
    }

    #[Route('/courses/{id}', name: 'app_course_show', methods: ['GET'])]
    public function course(string $id, MarketplaceCatalog $catalog): Response
    {
        foreach ($catalog->all() as $course) {
            if ($course['id'] === $id) {
                return $this->render('catalogue/show.html.twig', ['course' => $course, 'teacher' => $catalog->teachers()[$course['teacherId']]]);
            }
        }
        throw $this->createNotFoundException('Course not found.');
    }

    #[Route('/teachers/{id}', name: 'app_teacher_public', methods: ['GET'])]
    public function teacher(string $id, MarketplaceCatalog $catalog): Response
    {
        $teacher = $catalog->teachers()[$id] ?? null;
        if (!$teacher) throw $this->createNotFoundException('Teacher not found.');
        $courses = array_values(array_filter($catalog->all(), static fn ($course) => $course['teacherId'] === $id));
        return $this->render('teachers/show.html.twig', ['teacher' => $teacher, 'courses' => $courses]);
    }

    #[Route('/about', name: 'app_about', methods: ['GET'])]
    public function about(): Response
    {
        return $this->render('pages/about.html.twig');
    }

    #[Route('/checkout/{courseId}', name: 'app_checkout', methods: ['GET', 'POST'])]
    public function checkout(string $courseId, MarketplaceCatalog $catalog): Response
    {
        foreach ($catalog->all() as $course) {
            if ($course['id'] === $courseId) {
                return $this->render('pages/checkout.html.twig', ['course' => $course]);
            }
        }
        throw $this->createNotFoundException('Course not found.');
    }

    #[Route('/payment/{courseId}', name: 'app_payment_status', methods: ['GET', 'POST'])]
    public function payment(string $courseId, Request $request): Response
    {
        if ($request->isMethod('POST') && !$this->isCsrfTokenValid('checkout', $request->request->get('_token'))) {
            throw $this->createAccessDeniedException('Invalid checkout token.');
        }

        return $this->render('pages/payment.html.twig', [
            'courseId' => $courseId,
            'state' => $request->query->get('state', 'pending'),
        ]);
    }

    #[Route('/learn/{courseId}', name: 'app_learn', methods: ['GET'])]
    public function learn(string $courseId, MarketplaceCatalog $catalog): Response
    {
        foreach ($catalog->all() as $course) {
            if ($course['id'] === $courseId) return $this->render('pages/learn.html.twig', ['course' => $course]);
        }
        throw $this->createNotFoundException('Course not found.');
    }
}
