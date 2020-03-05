<?php

namespace Bone\User\Form\Transformer;

use Del\Entity\Country;
use Del\Factory\CountryFactory;
use Del\Form\Field\TransformerInterface;

class CountryTransformer implements TransformerInterface
{
    public function input($data): string
    {
        if ($data instanceof Country) {
            return $data->getIso();
        }

        return $data;
    }

    public function output(string $value)
    {
        return empty($value) ? '' : CountryFactory::generate($value);
    }

}