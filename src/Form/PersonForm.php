<?php

namespace Bone\User\Form;

use Bone\I18n\Form;
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
    use PersonFormTrait;

    public function init()
    {
        $this->addPersonFormFields($this);
        $submit = new Submit('submit');
        $submit->setValue('Update Profile');
        $this->addField($submit);
        $this->setFormRenderer(new HorizontalFormRenderer());
    }
}