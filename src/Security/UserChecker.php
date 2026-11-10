<?php

namespace App\Security;

use App\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAccountStatusException;
use Symfony\Component\Security\Core\User\UserCheckerInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class UserChecker implements UserCheckerInterface
{
    public function checkPreAuth(UserInterface $user): void
    {
        if (!$user instanceof User) {
            return;
        }

        if (!$user->isEmailVerified()) {
            throw new CustomUserMessageAccountStatusException(
                'Please verify your email address before logging in.'
            );
        }

        if ($user->getStatus() !== User::STATUS_ACTIVE) {
            throw new CustomUserMessageAccountStatusException(
                'Your account is not active. Please contact support.'
            );
        }
    }

    public function checkPostAuth(
        UserInterface $user,
        ?TokenInterface $token = null
    ): void {
        // No additional post-authentication checks for now.
    }
}
