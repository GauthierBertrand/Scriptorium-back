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

    public function prePersist(User $user, LifecycleEventArgs $event) 
    {
        $user->setPassword($this->hasher->hashPassword($user, $user->getPassword()));
    }

    public function preUpdate(User $user, LifecycleEventArgs $event) 
    {
        if (array_key_exists("password", $event->getEntityChangeSet())) {
            $user->setPassword($this->hasher->hashPassword($user, $user->getPassword()));
        }
    }

    public function preRemove(User $user, LifecycleEventArgs $event)
    {
        foreach($user->getSheets() as $sheet) {
            $this->manager->remove($sheet);
        }

        foreach($user->getGroups() as $group) {
            $this->manager->remove($group);
        }
    }
}