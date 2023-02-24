<?php

namespace App\Security\Voter;

use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\User\UserInterface;

class SheetVoter extends Voter
{
    public const EDIT = 'POST_EDIT';
    public const VIEW = 'GET_SHEET';

    protected function supports(string $attribute, $subject): bool
    {
        // replace with your own logic
        // https://symfony.com/doc/current/security/voters.html
        return in_array($attribute, [self::EDIT, self::VIEW])
            && $subject instanceof \App\Entity\Sheet;
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
            case self::EDIT:
                if ($user === $subject->getUser() OR in_array("ROLE_ADMIN", $user->getRoles())) {
                    return true;
                }
                break;
            case self::VIEW:
                if ($user === $subject->getUser()) {
                    return true;
                }
                break;
        }

        return false;
    }
}
