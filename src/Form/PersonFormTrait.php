<?php

namespace Bone\User\Form;

use Del\Form\Field\FieldInterface;
use Del\Form\Field\Hidden;
use Del\Form\Field\Select;
use Del\Form\Field\Text;
use Del\Form\FormInterface;
use Del\Form\Transformer\CountryTransformer;
use Del\Repository\CountryRepository;

trait PersonFormTrait
{
    protected array $disabledFields = [];

    public function addPersonFormFields(FormInterface $form): void
    {
        $firstName = new Text('firstname');
        $firstName->setLabel('First name');
        $firstName->setRequired(true);

        $middleName = new Text('middlename');
        $middleName->setLabel('Middle name');

        $lastName = new Text('lastname');
        $lastName->setLabel('Last name');
        $lastName->setRequired(true);

        $aka = new Text('aka');
        $aka->setLabel('A.K.A.');

        $dob = new Text\Date('dob', 'd/m/Y');
        $dob->setLabel('Date of Birth');
        $dob->setClass('form-control datepicker');
        $dob->setRequired(true);

        $birthPlace = new Text('birthplace');
        $birthPlace->setLabel('Birth place');

        $country = new Select('country');
        $country->setLabel('Country');
        $country->setTransformer(new CountryTransformer());
        $countryRepository = new CountryRepository();
        $countries = $countryRepository->findAllCountries();
        $country->setRequired(true);
        $country->setOption('', '');
        foreach ($countries as $c) {
            $country->setOption($c->getIso(), $c->getName());
        }

        $image = new Hidden('image');
        $image->setId('image');

        $this->addFieldIfEnabled('firstname', $firstName, $form);
        $this->addFieldIfEnabled('middlename', $middleName, $form);
        $this->addFieldIfEnabled('lastname', $lastName, $form);
        $this->addFieldIfEnabled('aka', $aka, $form);
        $this->addFieldIfEnabled('dob', $dob, $form);
        $this->addFieldIfEnabled('birthplace', $birthPlace, $form);
        $this->addFieldIfEnabled('country', $country, $form);
        $this->addFieldIfEnabled('image', $image, $form);
    }

    private function addFieldIfEnabled(string $name, FieldInterface $field, FormInterface $form): void
    {
        in_array($name, $this->disabledFields) ? null : $form->addField($field);
    }
}
