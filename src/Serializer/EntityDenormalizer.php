<?php

namespace App\Serializer;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

/**
 * Entity denormalizer
 * 
 * Pour que le Sérializer appelle ce service, au moment de la désérialisation
 * on implémente DenormalizerInterface
 */
class EntityDenormalizer implements DenormalizerInterface
{
    /** @var EntityManagerInterface **/
    protected $em;

    public function __construct(EntityManagerInterface $em)
    {
        // on récupère le service manager de Doctrine
        $this->em = $em;
    }

    /**
     * @inheritDoc
     * 
     * Si cette méthode renvoie "true", on appelle la méthode denormalize() plus bas
     * 
     */
    public function supportsDenormalization($data, $type, $format = null): bool
    {
        // si notre FQCN est de type App\Entity
        // ET que la donnée associée est un nombre
        return strpos($type, 'App\\Entity\\') === 0 && (is_numeric($data));
    }

    /**
     * @inheritDoc
     */
    public function denormalize($data, $class, $format = null, array $context = [])
    {
        // on retourne une entité via le Repository de l'entité
        // ->find() est un raccourci pour getRepository($className)->find($id).
        return $this->em->find($class, $data);
    }
}
