<?php

namespace Bone\User\Form;

use Bone\User\Form\Transformer\CountryTransformer;
use Del\Form\Field\Hidden;
use Del\Form\Field\Select;
use Del\Form\Field\Text;
use Del\Form\Field\Transformer\DateTimeTransformer;
use Del\Form\FormInterface;
use Del\Repository\CountryRepository;

trait PersonFormTrait
{
    /**
     * @param FormInterface $form
     */
    public function addPersonFormFields(FormInterface $form): void
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

        $form->addField($firstName);
        $form->addField($middleName);
        $form->addField($lastName);
        $form->addField($aka);
        $form->addField($dob);
        $form->addField($birthPlace);
        $form->addField($country);
        $form->addField($image);
    }
}
