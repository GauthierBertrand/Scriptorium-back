<?php

namespace App\Security\Voter;

use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\User\UserInterface;

class GroupVoter extends Voter
{
    public const DELETE = 'DELETE_USER';
    public const ADD = 'ADD_PLAYER';

    protected function supports(string $attribute, $subject): bool
    {
        // replace with your own logic
        // https://symfony.com/doc/current/security/voters.html
        return in_array($attribute, [self::DELETE, self::ADD])
            && $subject instanceof \App\Entity\Group;
    }

    protected function voteOnAttribute(string $attribute, $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();
        // if the user is anonymous, do not grant access
        if (!$user instanceof UserInterface) {
            return false;
        }

        // ... (check conditions and return true to grant permission) ...
        switch ($attribute) {
            case self::DELETE:
                if ($user === $subject->getGameMaster()) {
                    return true;
                }
                break;
            case self::ADD:
                if ($user !== $subject->getGameMaster() AND !$subject->getPlayers()->contains($user)) {
                    return true;
                }
                break;
        }

        return false;
    }
}
