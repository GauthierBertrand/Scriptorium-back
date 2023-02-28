<?php

namespace App\Controller\Admin;

use App\Admin\FormType\JsonEditorType;
use App\Entity\Sheet;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;

class SheetCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Sheet::class;
    }
    
    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')->onlyOnIndex(),
            TextField::new('character_name'),
            TextField::new('race_name'),
            TextField::new('religion_name'),
            TextareaField::new('description'),
            IntegerField::new('age'),
            IntegerField::new('level'),
            TextField::new('picture'),
            IntegerField::new('height'),
            IntegerField::new('weight'),
            TextField::new('hair'),
            TextareaField::new('encodestats')->setFormType(JsonEditorType::class)->setLabel("Stats"),
            AssociationField::new('user'),
            AssociationField::new('classe'),
            AssociationField::new('way_abilities'),
            AssociationField::new('racialAbility')
        ];
    }
    
}
