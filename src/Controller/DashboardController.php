<?php

namespace App\Controller;

use App\Entity\User;
use App\Service\MarketplaceCatalog;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class DashboardController extends AbstractController
{
    #[Route('/dashboard', name: 'app_dashboard', methods: ['GET'])]
    public function index(): Response
    {
        if ($this->isGranted(User::ROLE_ADMIN)) return $this->redirectToRoute('app_admin_dashboard');
        if ($this->isGranted(User::ROLE_TEACHER)) return $this->redirectToRoute('app_teacher_dashboard');
        if ($this->isGranted(User::ROLE_PARENT)) return $this->redirectToRoute('app_parent_dashboard');
        throw $this->createAccessDeniedException();
    }

    #[Route('/parent/dashboard', name: 'app_parent_dashboard', methods: ['GET'])]
    public function parent(MarketplaceCatalog $catalog): Response
    {
        $this->denyAccessUnlessGranted(User::ROLE_PARENT);
        $purchased = array_values(array_filter($catalog->all(), static fn ($course) => $course['id'] === 'english-everyday'));
        return $this->render('dashboard/index.html.twig', ['role'=>'parent','section'=>'dashboard','courses'=>$purchased]);
    }

    #[Route('/parent/{section}', name: 'app_parent_section', requirements: ['section'=>'children|courses|orders|notifications|profile|settings'], methods: ['GET'])]
    public function parentSection(string $section, MarketplaceCatalog $catalog): Response
    {
        $this->denyAccessUnlessGranted(User::ROLE_PARENT);
        $purchased = array_values(array_filter($catalog->all(), static fn ($course) => $course['id'] === 'english-everyday'));
        return $this->render('dashboard/index.html.twig', ['role'=>'parent','section'=>$section,'courses'=>$purchased]);
    }

    #[Route('/parent/children/{id}', name: 'app_parent_child', methods: ['GET'])]
    public function child(string $id, MarketplaceCatalog $catalog): Response
    {
        $this->denyAccessUnlessGranted(User::ROLE_PARENT);
        $purchased = array_values(array_filter($catalog->all(), static fn ($course) => $course['id'] === 'english-everyday'));
        return $this->render('dashboard/index.html.twig', ['role'=>'parent','section'=>'child','childId'=>$id,'courses'=>$purchased]);
    }

    #[Route('/teacher/dashboard', name: 'app_teacher_dashboard', methods: ['GET'])]
    public function teacher(MarketplaceCatalog $catalog): Response
    {
        $this->denyAccessUnlessGranted(User::ROLE_TEACHER);
        $teacherCourses = array_values(array_filter($catalog->all(), static fn ($course) => $course['teacherId'] === 'amira'));
        return $this->render('dashboard/index.html.twig', ['role'=>'teacher','section'=>'dashboard','courses'=>$teacherCourses]);
    }

    #[Route('/teacher/{section}', name: 'app_teacher_section', requirements: ['section'=>'courses|notifications|profile|sales|students|settings'], methods: ['GET'])]
    public function teacherSection(string $section, MarketplaceCatalog $catalog): Response
    {
        $this->denyAccessUnlessGranted(User::ROLE_TEACHER);
        $teacherCourses = array_values(array_filter($catalog->all(), static fn ($course) => $course['teacherId'] === 'amira'));
        return $this->render('dashboard/index.html.twig', ['role'=>'teacher','section'=>$section,'courses'=>$teacherCourses]);
    }

    #[Route('/teacher/courses/new', name: 'app_teacher_course_new', methods: ['GET','POST'])]
    public function newCourse(): Response
    {
        $this->denyAccessUnlessGranted(User::ROLE_TEACHER);
        return $this->render('dashboard/course_new.html.twig');
    }

    #[Route('/admin/dashboard', name: 'app_admin_dashboard', methods: ['GET'])]
    public function admin(): Response
    {
        $this->denyAccessUnlessGranted(User::ROLE_ADMIN);
        return $this->render('dashboard/index.html.twig', ['role'=>'admin','section'=>'dashboard','courses'=>[]]);
    }

    #[Route('/admin/courses/pending', name: 'app_admin_pending_courses', methods: ['GET'])]
    public function adminPendingCourses(): Response
    {
        $this->denyAccessUnlessGranted(User::ROLE_ADMIN);
        return $this->render('dashboard/index.html.twig', ['role'=>'admin','section'=>'pending-courses','courses'=>[]]);
    }

    #[Route('/admin/courses/pending/{id}', name: 'app_admin_pending_course', methods: ['GET'])]
    public function adminPendingCourse(string $id): Response
    {
        $this->denyAccessUnlessGranted(User::ROLE_ADMIN);
        return $this->render('dashboard/index.html.twig', ['role'=>'admin','section'=>'pending-course','courses'=>[]]);
    }

    #[Route('/admin/{section}', name: 'app_admin_section', requirements: ['section'=>'audit-logs|categories|courses|orders|payments|reports|school-levels|settings|subjects|users'], methods: ['GET'])]
    public function adminSection(string $section): Response
    {
        $this->denyAccessUnlessGranted(User::ROLE_ADMIN);
        return $this->render('dashboard/index.html.twig', ['role'=>'admin','section'=>$section,'courses'=>[]]);
    }
}
