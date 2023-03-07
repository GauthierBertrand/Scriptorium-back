<?php

namespace App\Controller\Admin;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ArrayField;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;

class UserCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return User::class;
    }

    public function configureFields(string $pageName): iterable
    {
        // dd($pageName);
        $passwordField = TextField::new('password')->setFormType(PasswordType::class)->onlyOnForms();

        switch ($pageName) {
            case "edit":
                $passwordField = TextField::new('plainTextPassword')->setFormType(PasswordType::class)->onlyOnForms()->setFormTypeOption("required", false)->setLabel("Password");
        }

        return [
            IdField::new('id')->onlyOnIndex(),
            TextField::new('email'),
            TextField::new('pseudo'),
            $passwordField,
            ArrayField::new('roles'),
            BooleanField::new('is_verified')
        ];
    }
}
