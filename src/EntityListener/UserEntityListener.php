<?php

namespace App\EntityListener;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\Event\LifecycleEventArgs;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserEntityListener {
    /**
     * @var UserPasswordHasherInterface $passwordHasher
     */
    private $hasher;
    
    /**
     * @var EntityManagerInterface $manager
     */
    private $manager;

    /**
     * @param UserPasswordHasherInterface $passwordHasher
     * @param EntityManagerInterface $manager
     * 
     * @return void
     */
    public function __construct(UserPasswordHasherInterface $passwordHasher, EntityManagerInterface $manager)
    {
        $this->hasher = $passwordHasher;
        $this->manager = $manager;
    }

    /**
     * @param User $user
     * @param LifecycleEventArgs $event
     * 
     * @return void
     */
    public function prePersist(User $user, LifecycleEventArgs $event) 
    {
        $user->setPassword($this->hasher->hashPassword($user, $user->getPassword()));
    }
    
    /**
     * Prevent multiple password hashes on each persist
     *
     * @param User $user
     * @param LifecycleEventArgs $event
     * 
     * @return void
     */
    public function preUpdate(User $user, LifecycleEventArgs $event) 
    {
        if (array_key_exists("password", $event->getEntityChangeSet())) {
            $user->setPassword($this->hasher->hashPassword($user, $user->getPassword()));
        }
    }

    /**
     * @param User $user
     * @param LifecycleEventArgs $event
     * 
     * @return void
     */
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