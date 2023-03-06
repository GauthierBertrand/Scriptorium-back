<?php

namespace App\EntityListener;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\Event\LifecycleEventArgs;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserEntityListener {

    private $hasher;
    private $manager;

    public function __construct(UserPasswordHasherInterface $passwordHasher, EntityManagerInterface $manager)
    {
        $this->hasher = $passwordHasher;
        $this->manager = $manager;
    }

    public function preUpdate(User $user, LifecycleEventArgs $event) 
    {
        $user = $event->getObject();
        $user->setPassword($this->hasher->hashPassword($user, $user->getPassword()));
    }

    public function prePersist(User $user, LifecycleEventArgs $event) 
    {
        $user = $event->getObject();
        $user->setPassword($this->hasher->hashPassword($user, $user->getPassword()));
    }

    public function preRemove(User $user, LifecycleEventArgs $event)
    {
        $user = $event->getObject();

        foreach($user->getSheets() as $sheet) {
            $this->manager->remove($sheet);
        }
    }
}