<?php

namespace Bone\User\Form;

use Bone\Contracts\Service\TranslatorInterface;
use Bone\I18n\Form;
use Del\Form\Field\Hidden;
use Del\Form\Field\Select;
use Del\Form\Field\Submit;
use Del\Form\Field\Text;
use Del\Form\Field\Transformer\DateTimeTransformer;
use Del\Form\Renderer\HorizontalFormRenderer;
use Del\Form\Transformer\CountryTransformer;
use Del\Repository\CountryRepository;

class PersonForm extends Form
{
    use PersonFormTrait;

    public function __construct($name, TranslatorInterface $translator, $disabledFields = [])
    {
        $this->disabledFields = $disabledFields;
        parent::__construct($name, $translator);
    }

    public function init(): void
    {
        $this->addPersonFormFields($this);
        $submit = new Submit('submit');
        $submit->setValue('Update Profile');
        $this->addField($submit);
        $this->setFormRenderer(new HorizontalFormRenderer());
    }
}
