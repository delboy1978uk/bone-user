<?php

namespace Bone\Test\User\Form\Transformer;

use Bone\User\Form\PersonForm;
use Bone\User\Form\Transformer\CountryTransformer;
use Codeception\TestCase\Test;
use Del\Entity\Country;
use Del\Factory\CountryFactory;

class CountryTransformerTest extends Test
{

    public function testTransformer()
    {
        $transformer = new CountryTransformer();
        $this->assertEquals('BE', $transformer->input('BE'));
        $this->assertEquals('BE', $transformer->input(CountryFactory::generate('BE')));
        $this->assertEquals('', $transformer->output(''));
        $this->assertInstanceOf(Country::class, $transformer->output('BE'));

    }
}
