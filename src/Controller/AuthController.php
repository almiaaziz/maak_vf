<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use App\Entity\User;
use App\Entity\UserToken;
use App\Service\UserTokenService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\RateLimiter\RateLimiterFactory;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use App\Entity\ParentProfile;
use App\Entity\TeacherProfile;

final class AuthController extends AbstractController
{
    #[Route('/register', name: 'app_register', methods: ['GET'])]
    public function registerChoice(): Response
    {
        return $this->render('auth/register.html.twig');
    }

    #[Route('/register/parent', name: 'app_register_parent', methods: ['GET', 'POST'])]
    public function registerParent(
        Request $request,
        EntityManagerInterface $entityManager,
        UserPasswordHasherInterface $passwordHasher,
        UserTokenService $tokenService,
        #[Autowire(service: 'limiter.registration')]
        RateLimiterFactory $registrationLimiter
    ): Response {
        return $this->register(
            $request,
            $entityManager,
            $passwordHasher,
            $tokenService,
            $registrationLimiter,
            User::ROLE_PARENT
        );
    }

    #[Route('/register/teacher', name: 'app_register_teacher', methods: ['GET', 'POST'])]
    public function registerTeacher(
        Request $request,
        EntityManagerInterface $entityManager,
        UserPasswordHasherInterface $passwordHasher,
        UserTokenService $tokenService,
        #[Autowire(service: 'limiter.registration')]
        RateLimiterFactory $registrationLimiter
    ): Response {
        return $this->register(
            $request,
            $entityManager,
            $passwordHasher,
            $tokenService,
            $registrationLimiter,
            User::ROLE_TEACHER
        );
    }

    private function register(
        Request $request,
        EntityManagerInterface $entityManager,
        UserPasswordHasherInterface $passwordHasher,
        UserTokenService $tokenService,
        RateLimiterFactory $registrationLimiter,
        string $role
    ): Response {
        if ($request->isMethod('POST')) {
            $ip = $request->getClientIp() ?? 'unknown';
            $limit = $registrationLimiter->create($ip)->consume(1);

            if (!$limit->isAccepted()) {
                return new Response(
                    'Too many registration attempts. Please try again later.',
                    Response::HTTP_TOO_MANY_REQUESTS
                );
            }

            if (!$this->isCsrfTokenValid(
                'registration',
                $request->request->get('_csrf_token')
            )) {
                throw $this->createAccessDeniedException('Invalid CSRF token.');
            }

            $email = strtolower(trim((string) $request->request->get('email')));
            $password = (string) $request->request->get('password');
            $confirmPassword = (string) $request->request->get('confirm_password');
            $firstName = trim((string) $request->request->get('first_name'));
            $lastName = trim((string) $request->request->get('last_name'));
            $phone = trim((string) $request->request->get('phone'));

            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $this->addFlash('error', 'Please enter a valid email address.');
                return $this->redirectToRoute($this->registrationRoute($role));
            }

            if ($entityManager->getRepository(User::class)->findOneBy(['email' => $email])) {
                $this->addFlash('error', 'An account with this email already exists.');
                return $this->redirectToRoute($this->registrationRoute($role));
            }

            if ($firstName === '' || $lastName === '') {
                $this->addFlash('error', 'First name and last name are required.');
                return $this->redirectToRoute($this->registrationRoute($role));
            }

            if (strlen($password) < 8) {
                $this->addFlash('error', 'Your password must contain at least 8 characters.');
                return $this->redirectToRoute($this->registrationRoute($role));
            }

            if ($password !== $confirmPassword) {
                $this->addFlash('error', 'The passwords do not match.');
                return $this->redirectToRoute($this->registrationRoute($role));
            }

            if ($request->request->get('terms') !== '1') {
                $this->addFlash('error', 'You must accept the terms and conditions.');
                return $this->redirectToRoute($this->registrationRoute($role));
            }

            $user = new User();
            $user->setEmail($email);
            $user->setFirstName($firstName);
            $user->setLastName($lastName);
            $user->setPhone($phone !== '' ? $phone : null);
            $user->setRoles([$role]);
            $user->setStatus(User::STATUS_PENDING_VERIFICATION);
            $user->setTermsAcceptedAt(new \DateTimeImmutable());
            $user->setPassword(
                $passwordHasher->hashPassword($user, $password)
            );

            $entityManager->persist($user);

            if ($role === User::ROLE_PARENT) {
                $profile = new ParentProfile();
                $profile->setUser($user);

                $address = trim((string) $request->request->get('address'));
                $profile->setAddress($address !== '' ? $address : null);

                $entityManager->persist($profile);
            }

            if ($role === User::ROLE_TEACHER) {
                $university = trim((string) $request->request->get('university'));
                $fieldOfStudy = trim((string) $request->request->get('field_of_study'));

                if ($university === '' || $fieldOfStudy === '') {
                    $this->addFlash('error', 'University and field of study are required.');
                    return $this->redirectToRoute($this->registrationRoute($role));
                }

                $profile = new TeacherProfile();
                $profile->setUser($user);
                $profile->setUniversity($university);
                $profile->setFieldOfStudy($fieldOfStudy);

                $degree = trim((string) $request->request->get('academic_degree'));
                $profile->setAcademicDegree($degree !== '' ? $degree : null);

                $entityManager->persist($profile);
            }

            $entityManager->flush();

            $tokenService->createEmailVerificationToken($user);

            $this->addFlash(
                'success',
                'Your account has been created. Please check your email to verify it.'
            );

            return $this->redirectToRoute('app_login');
        }

        $template = $role === User::ROLE_PARENT
            ? 'registration/parent.html.twig'
            : 'registration/teacher.html.twig';

        return $this->render($template);
    }

    private function registrationRoute(string $role): string
    {
        return $role === User::ROLE_PARENT
            ? 'app_register_parent'
            : 'app_register_teacher';
    }

    #[Route('/login', name: 'app_login', methods: ['GET', 'POST'])]
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        if ($this->getUser()) {
            return $this->redirectToRoute('app_dashboard');
        }

        return $this->render('security/login.html.twig', [
            'last_username' => $authenticationUtils->getLastUsername(),
            'error' => $authenticationUtils->getLastAuthenticationError(),
        ]);
    }

    #[Route('/logout', name: 'app_logout', methods: ['GET', 'POST'])]
    public function logout(): void
    {
        // Symfony intercepts this route and invalidates the session.
        throw new \LogicException('This method should never be reached.');
    }

    #[Route('/verify-email/{token}', name: 'app_verify_email', methods: ['GET'])]
    public function verify(
        string $token,
        Request $request,
        UserTokenService $tokenService,
        EntityManagerInterface $entityManager,
        #[Autowire(service: 'limiter.email_verification')]
        RateLimiterFactory $verificationLimiter
    ): Response {
        $ip = $request->getClientIp() ?? 'unknown';

        if (!$verificationLimiter->create($ip)->consume(1)->isAccepted()) {
            return new Response(
                'Too many verification attempts. Please try again later.',
                Response::HTTP_TOO_MANY_REQUESTS
            );
        }

        $verificationToken = $tokenService->findValidToken(
            $token,
            UserToken::PURPOSE_EMAIL_VERIFICATION
        );

        if (!$verificationToken) {
            $this->addFlash('error', 'This verification link is invalid, expired, or already used.');
            return $this->redirectToRoute('app_login');
        }

        $user = $verificationToken->getUser();

        if (!$user instanceof User) {
            throw $this->createNotFoundException();
        }

        $user->setEmailVerifiedAt(new \DateTimeImmutable());

        if ($user->getStatus() === User::STATUS_PENDING_VERIFICATION) {
            $user->setStatus(User::STATUS_ACTIVE);
        }

        $tokenService->consumeToken($verificationToken);
        $entityManager->flush();

        $this->addFlash(
            'success',
            'Your email has been verified. You can now log in.'
        );

        return $this->redirectToRoute('app_login');
    }

    #[Route('/forgot-password', name: 'app_forgot_password', methods: ['GET', 'POST'])]
    public function requestReset(
        Request $request,
        EntityManagerInterface $entityManager,
        UserTokenService $tokenService,
        #[Autowire(service: 'limiter.password_reset')]
        RateLimiterFactory $resetLimiter
    ): Response {
        if ($request->isMethod('POST')) {
            $ip = $request->getClientIp() ?? 'unknown';

            if (!$resetLimiter->create($ip)->consume(1)->isAccepted()) {
                return new Response(
                    'Too many password reset requests. Please try again later.',
                    Response::HTTP_TOO_MANY_REQUESTS
                );
            }

            if (!$this->isCsrfTokenValid(
                'forgot_password',
                $request->request->get('_csrf_token')
            )) {
                throw $this->createAccessDeniedException('Invalid CSRF token.');
            }

            $email = strtolower(trim((string) $request->request->get('email')));

            if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $user = $entityManager
                    ->getRepository(User::class)
                    ->findOneBy(['email' => $email]);

                if (
                    $user instanceof User
                    && $user->isEmailVerified()
                    && $user->getStatus() === User::STATUS_ACTIVE
                ) {
                    $tokenService->createPasswordResetToken($user);
                }
            }

            $this->addFlash(
                'success',
                'If an eligible account exists with that email, a password reset link will be sent.'
            );


            return $this->redirectToRoute('app_forgot_password');
        }
        return $this->render('password_reset/request.html.twig');
    }

    #[Route('/reset-password/{token}', name: 'app_reset_password', methods: ['GET', 'POST'])]
    public function reset(
        string $token,
        Request $request,
        UserTokenService $tokenService,
        UserPasswordHasherInterface $passwordHasher,
        EntityManagerInterface $entityManager
    ): Response {
        $resetToken = $tokenService->findValidToken(
            $token,
            UserToken::PURPOSE_PASSWORD_RESET
        );

        if (!$resetToken) {
            $this->addFlash(
                'error',
                'This password reset link is invalid, expired, or already used.'
            );
            return $this->redirectToRoute('app_forgot_password');
        }

        if ($request->isMethod('POST')) {
            if (!$this->isCsrfTokenValid(
                'reset_password',
                $request->request->get('_csrf_token')
            )) {
                throw $this->createAccessDeniedException('Invalid CSRF token.');
            }

            $password = (string) $request->request->get('password');
            $confirmPassword = (string) $request->request->get('confirm_password');

            if (strlen($password) < 8) {
                $this->addFlash(
                    'error',
                    'Your password must contain at least 8 characters.'
                );

                return $this->redirectToRoute('app_reset_password', [
                    'token' => $token,
                ]);
            }

            if ($password !== $confirmPassword) {
                $this->addFlash('error', 'The passwords do not match.');

                return $this->redirectToRoute('app_reset_password', [
                    'token' => $token,
                ]);
            }

            $user = $resetToken->getUser();

            if (!$user instanceof User) {
                throw $this->createNotFoundException();
            }

            $user->setPassword(
                $passwordHasher->hashPassword($user, $password)
            );

            $tokenService->consumeToken($resetToken);

            // Invalidate all other outstanding password-reset tokens.
            $otherTokens = $entityManager
                ->getRepository(UserToken::class)
                ->findBy([
                    'user' => $user,
                    'purpose' => UserToken::PURPOSE_PASSWORD_RESET,
                    'usedAt' => null,
                ]);

            foreach ($otherTokens as $otherToken) {
                $otherToken->setUsedAt(new \DateTimeImmutable());
            }

            $entityManager->flush();

            $this->addFlash(
                'success',
                'Your password has been changed. You can now log in.'
            );

            return $this->redirectToRoute('app_login');
        }

        return $this->render('password_reset/reset.html.twig', [
            'token' => $token,
        ]);
    }
}
