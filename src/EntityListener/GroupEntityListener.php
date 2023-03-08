<?php

namespace App\EntityListener;

use App\Entity\Group;
use App\Utils\GenerateRandomCode;
use Doctrine\Persistence\Event\LifecycleEventArgs;

class GroupEntityListener {
    /**
     * @var GenerateRandomCode $generator
     */
    private $generator;

    /**
     * @param GenerateRandomCode $generator
     */
    public function __construct(GenerateRandomCode $generator)
    {
        $this->generator = $generator;
    }

    /**
     * @param Group $group
     * @param LifecycleEventArgs $event
     */
    public function prePersist(Group $group, LifecycleEventArgs $event) 
    {
        $code = $this->generator->generate($group->getGameMaster());
        $group->setCodeRegister($code);
    }
}