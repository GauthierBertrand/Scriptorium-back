<?php
namespace App\Admin\FormType;

use App\Admin\DataTransformer\JsonToPrettyJsonTransformer;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;

/**
 * Custom FormType for easyadmin's text area field to print json correctly
 */
class JsonEditorType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);
        $builder->addViewTransformer(new JsonToPrettyJsonTransformer());
    }

    public function getParent()
    {
        return TextareaType::class;
    }
}