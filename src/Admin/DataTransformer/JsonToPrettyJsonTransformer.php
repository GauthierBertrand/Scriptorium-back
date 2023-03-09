<?php

namespace App\Admin\DataTransformer;

use Symfony\Component\Form\DataTransformerInterface;

/**
 * Formatting a json for easyadmin use
 */
class JsonToPrettyJsonTransformer implements DataTransformerInterface
{
    public function transform($value)
    {
        return json_encode(json_decode($value), JSON_PRETTY_PRINT);
    }

    public function reverseTransform($value)
    {
        return json_encode(json_decode($value));
    }
}