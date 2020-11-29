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
    /** @var array $disabledFields */
    protected $disabledFields = [];

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

        in_array('firstname', $this->disabledFields) ? null : $form->addField($firstName);
        in_array('middlename', $this->disabledFields) ? null : $form->addField($middleName);
        in_array('lastname', $this->disabledFields) ? null : $form->addField($lastName);
        in_array('aka', $this->disabledFields) ? null : $form->addField($aka);
        in_array('dob', $this->disabledFields) ? null : $form->addField($dob);
        in_array('birthplace', $this->disabledFields) ? null : $form->addField($birthPlace);
        in_array('country', $this->disabledFields) ? null : $form->addField($country);
        in_array('image', $this->disabledFields) ? null : $form->addField($image);
    }
}
