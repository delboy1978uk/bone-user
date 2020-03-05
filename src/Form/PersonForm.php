<?php

namespace Bone\User\Form;

use Bone\Form;
use Bone\User\Form\Transformer\CountryTransformer;
use Del\Form\Field\Hidden;
use Del\Form\Field\Select;
use Del\Form\Field\Submit;
use Del\Form\Field\Text;
use Del\Form\Field\Transformer\DateTimeTransformer;
use Del\Form\Renderer\HorizontalFormRenderer;
use Del\Repository\CountryRepository;

class PersonForm extends Form
{
    public function init()
    {
        $firstName = new Text('firstname');
        $firstName->setLabel('First name');

        $middleName = new Text('middlename');
        $middleName->setLabel('Middle name');

        $lastName = new Text('lastname');
        $lastName->setLabel('Last name');

        $aka = new Text('aka');
        $aka->setLabel('A.K.A.');

        $dob = new Text('dob');
        $dob->setLabel('Date of Birth');
        $dob->setClass('form-control datepicker');
        $dob->setTransformer(new DateTimeTransformer('d/m/Y'));

        $birthPlace = new Text('birthplace');
        $birthPlace->setLabel('Birth place');

        $country = new Select('country');
        $country->setLabel('Country');
        $country->setTransformer(new CountryTransformer());
        $countryRepository = new CountryRepository();
        $countries = $countryRepository->findAllCountries();
        $country->setOption('', '');
        foreach ($countries as $c) {
            $country->setOption($c->getIso(), $c->getName());
        }

        $image = new Hidden('image');
        $image->setId('image');

        $submit = new Submit('submit');
        $submit->setValue('Update Profile');

        $this->addField($firstName);
        $this->addField($middleName);
        $this->addField($lastName);
        $this->addField($aka);
        $this->addField($dob);
        $this->addField($birthPlace);
        $this->addField($country);
        $this->addField($image);
        $this->addField($submit);
        $this->setFormRenderer(new HorizontalFormRenderer());
    }
}