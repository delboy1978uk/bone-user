<?php

namespace Bone\User\Form;

use Bone\Form;
use Del\Form\Field\Submit;
use Del\Form\Field\Text\EmailAddress;
use Del\Form\Field\Text\Password;
use Del\Form\Filter\Adapter\FilterAdapterZf;
use Del\Form\Renderer\HorizontalFormRenderer;
use Laminas\Filter\StringToLower;

class RegistrationForm extends Form
{
    public function init()
    {
        $label = $this->getTranslator()->translate('form.email.label', 'user');
        $error = $this->getTranslator()->translate('form.email.error', 'user');
        $placeholder = $this->getTranslator()->translate('form.email.placeholder', 'user');
        $email = new EmailAddress('email');
        $email->setRequired(true);
        $email->setAttribute('size', 40);
        $email->setId('regemail');
        $email->setLabel($label);
        $email->setCustomErrorMessage($error);


        $label = $this->getTranslator()->translate('form.password.label', 'user');
        $error = $this->getTranslator()->translate('form.password.error', 'user');
        $placeholder = $this->getTranslator()->translate('form.password.placeholder', 'user');
        $password = new Password('password');
        $password->setRequired(true);
        $password->setClass('form-control password');
        $password->setLabel($label);
        $password->setId('regpassword');
        $password->setAttribute('size', 40);
        $password->setAttribute('placeholder', $placeholder);
        $password->setCustomErrorMessage($error);

        $label = $this->getTranslator()->translate('form.confirm.label', 'user');
        $error = $this->getTranslator()->translate('form.confirm.error', 'user');
        $placeholder = $this->getTranslator()->translate('form.confirm.placeholder', 'user');
        $confirm = new Password('confirm');
        $confirm->setRequired(true);
        $confirm->setLabel($label);
        $confirm->setAttribute('size', 40);
        $confirm->setAttribute('placeholder', $placeholder);
        $confirm->setCustomErrorMessage($error);

        $label = $this->getTranslator()->translate('form.submit.label', 'user');
        $submit = new Submit('submit');
        $submit->setValue($label);

        $stringToLower = new StringToLower();
        $email->addFilter(new FilterAdapterZf($stringToLower));

        $renderer = new HorizontalFormRenderer();

        $this->addField($email);
        $this->addField($password);
        $this->addField($confirm);
        $this->addField($submit);
        $this->setFormRenderer($renderer);
    }

}