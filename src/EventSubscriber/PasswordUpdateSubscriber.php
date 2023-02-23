<?php

namespace App\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use EasyCorp\Bundle\EasyAdminBundle\Event\BeforeEntityUpdatedEvent;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class PasswordUpdateSubscriber implements EventSubscriberInterface
{
    private $passwordHasher;

    public function __construct(UserPasswordHasherInterface $passwordHasher)
    {
        $this->passwordHasher = $passwordHasher;
    }

    public function setPasswordHashed(BeforeEntityUpdatedEvent $event): void
    {
        $user = $event->getEntityInstance();

        $user->setPassword($this->passwordHasher->hashPassword($user, $user->getPassword()));
        // dd($user);
    }

    public static function getSubscribedEvents(): array
    {
        return [
            BeforeEntityUpdatedEvent::class => 'setPasswordHashed',
        ];
    }
}
