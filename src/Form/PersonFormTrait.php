<?php

namespace Bone\User\Form;

use Bone\User\Form\Transformer\CountryTransformer;
use Del\Form\Field\FieldInterface;
use Del\Form\Field\Hidden;
use Del\Form\Field\Select;
use Del\Form\Field\Text;
use Del\Form\Field\Transformer\DateTimeTransformer;
use Del\Form\FormInterface;
use Del\Repository\CountryRepository;

trait PersonFormTrait
{
    /** @var array $disabledFields */
    protected $disabledFields = [];

    /**
     * @param FormInterface $form
     */
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

        $dob = new Text('dob');
        $dob->setLabel('Date of Birth');
        $dob->setClass('form-control datepicker');
        $dob->setTransformer(new DateTimeTransformer('d/m/Y'));
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
        $this->addFieldIfEnabled('middlename', $firstName, $form);
        $this->addFieldIfEnabled('lastname', $firstName, $form);
        $this->addFieldIfEnabled('aka', $firstName, $form);
        $this->addFieldIfEnabled('dob', $firstName, $form);
        $this->addFieldIfEnabled('birthplace', $firstName, $form);
        $this->addFieldIfEnabled('country', $firstName, $form);
        $this->addFieldIfEnabled('image', $firstName, $form);
    }

    private function addFieldIfEnabled(string $name, FieldInterface $field, FormInterface $form)
    {
        in_array($name, $this->disabledFields) ? null : $form->addField($field);
    }
}
