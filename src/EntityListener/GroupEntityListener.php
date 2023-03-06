<?php

namespace App\EntityListener;

use App\Entity\Group;
use App\Utils\GenerateRandomCode;
use Doctrine\Persistence\Event\LifecycleEventArgs;

class GroupEntityListener {
    private $generator;

    public function __construct(GenerateRandomCode $generator)
    {
        $this->generator = $generator;
    }

    public function prePersist(Group $group, LifecycleEventArgs $event) 
    {
        $code = $this->generator->generate($group->getGameMaster());
        $group->setCodeRegister($code);
    }
}