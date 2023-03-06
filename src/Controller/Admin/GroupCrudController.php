<?php

namespace App\Controller\Admin;

use App\Entity\User;
use App\Entity\Group;

use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;

class GroupCrudController extends AbstractCrudController
{
    private $repository;

    public function __construct(EntityManagerInterface $manager)
    {
        $this->repository = $manager->getRepository(User::class);
    }

    public static function getEntityFqcn(): string
    {
        return Group::class;
    }
    
    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')->onlyOnIndex(),
            AssociationField::new('game_master'),
            TextField::new('name'),
            TextField::new('code_register')->onlyOnIndex(),
            AssociationField::new('players')
        ];
    }
}
