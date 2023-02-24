<?php

namespace App\EventSubscriber;

use App\Entity\Sheet;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use EasyCorp\Bundle\EasyAdminBundle\Event\BeforeEntityUpdatedEvent;
use EasyCorp\Bundle\EasyAdminBundle\Event\BeforeEntityPersistedEvent;
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
        if ($event->getEntityInstance() instanceof Sheet) {
            return;
        }

        $user = $event->getEntityInstance();

        $user->setPassword($this->passwordHasher->hashPassword($user, $user->getPassword()));
    }

    public function createPasswordHashed(BeforeEntityPersistedEvent $event): void
    {
        if ($event->getEntityInstance() instanceof Sheet) {
            return;
        }

        $user = $event->getEntityInstance();

        $user->setPassword($this->passwordHasher->hashPassword($user, $user->getPassword()));
    }

    public static function getSubscribedEvents(): array
    {
        return [
            BeforeEntityUpdatedEvent::class => 'setPasswordHashed',
            BeforeEntityPersistedEvent::class => 'createPasswordHashed'
        ];
    }
}
