<?php

namespace App\Utils;

use Symfony\Component\Serializer\Exception\NotEncodableValueException;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Serializer\Exception\NotNormalizableValueException;

class CheckSerializer {
    /**
     * @var SerializerInterface
     */
    private $serializer;

    public function __construct(SerializerInterface $serializer)
    {
        $this->serializer = $serializer;
    }

    /**
     * Check la déserialisation d'une entité donnée
     *
     * @param string $jsonContent
     * @param string $entityFqcn
     * @return Entity|string
     */
    public function serializeValidation(string $jsonContent, $entityFqcn, $options = [])
    {
        try {
            $entity = $this->serializer->deserialize($jsonContent, $entityFqcn, 'json', $options);

            return $entity;
        } catch (NotNormalizableValueException $exception) {
            return $exception->getMessage();

        } catch (NotEncodableValueException $exception) {
            return $exception->getMessage();
            
        }
    }
}