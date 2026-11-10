<?php

namespace App\Service;

use App\Entity\User;
use App\Entity\UserToken;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class UserTokenService
{

    public function __construct(
        private EntityManagerInterface $entityManager,
        private MailerInterface $mailer,
        private UrlGeneratorInterface $urlGenerator,
        #[Autowire('%env(MAILER_FROM)%')]
        private string $mailerFrom,
    ) {
    }

    public function createEmailVerificationToken(User $user): void
    {
        $this->invalidateTokens(
            $user,
            UserToken::PURPOSE_EMAIL_VERIFICATION
        );

        $this->createAndSend(
            $user,
            UserToken::PURPOSE_EMAIL_VERIFICATION,
            new \DateInterval('PT24H'),
            'app_verify_email',
            'Verify your MAAK email address',
            'Please verify your email address using the following link:'
        );
    }

    public function createPasswordResetToken(User $user): void
    {
        $this->invalidateTokens(
            $user,
            UserToken::PURPOSE_PASSWORD_RESET
        );

        $this->createAndSend(
            $user,
            UserToken::PURPOSE_PASSWORD_RESET,
            new \DateInterval('PT1H'),
            'app_reset_password',
            'Reset your MAAK password',
            'You can reset your password using the following link:'
        );
    }

    private function createAndSend(
        User $user,
        string $purpose,
        \DateInterval $lifetime,
        string $route,
        string $subject,
        string $message
    ): void {
        $plainToken = bin2hex(random_bytes(32));

        $token = new UserToken();
        $token->setUser($user);
        $token->setTokenHash(hash('sha256', $plainToken));
        $token->setPurpose($purpose);
        $token->setExpiresAt(
            (new \DateTimeImmutable())->add($lifetime)
        );

        $this->entityManager->persist($token);
        $this->entityManager->flush();

        $url = $this->urlGenerator->generate(
            $route,
            
            ['token' => $plainToken],
            UrlGeneratorInterface::ABSOLUTE_URL
        );

        $email = (new Email())
            ->from($this->mailerFrom)
            ->to($user->getEmail())
            ->subject($subject)
            ->text($message . "\n\n" . $url . "\n\nIf you did not request this, ignore this email.");

        $this->mailer->send($email);
    }

    private function invalidateTokens(User $user, string $purpose): void
    {
        $tokens = $this->entityManager
            ->getRepository(UserToken::class)
            ->findBy([
                'user' => $user,
                'purpose' => $purpose,
                'usedAt' => null,
            ]);

        foreach ($tokens as $token) {
            $token->setUsedAt(new \DateTimeImmutable());
        }

        $this->entityManager->flush();
    }

    public function findValidToken(
        string $plainToken,
        string $purpose
    ): ?UserToken {
        $hash = hash('sha256', $plainToken);

        $token = $this->entityManager
            ->getRepository(UserToken::class)
            ->findOneBy([
                'tokenHash' => $hash,
                'purpose' => $purpose,
            ]);

        if (!$token instanceof UserToken || !$token->isValid()) {
            return null;
        }

        return $token;
    }

    public function consumeToken(UserToken $token): void
    {
        $token->setUsedAt(new \DateTimeImmutable());
        $this->entityManager->flush();
    }
}
