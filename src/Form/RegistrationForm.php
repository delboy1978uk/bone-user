<?php

namespace BoneMvc\Module\BoneMvcUser\Form;

use Bone\Form;
use Del\Form\Field\Submit;
use Del\Form\Field\Text\EmailAddress;
use Del\Form\Field\Text\Password;
use Del\Form\Filter\Adapter\FilterAdapterZf;
use Del\Form\Renderer\HorizontalFormRenderer;
use Zend\Filter\StringToLower;

class RegistrationForm extends Form
{
    public function init()
    {
        $label = $this->getTranslator()->translate('form.email.label', 'user');
        $error = $this->getTranslator()->translate('form.email.error', 'user');
        $placeholder = $this->getTranslator()->translate('form.email.placeholder', 'user');
        $email = new EmailAddress('email');
        $email->setRequired(true)
            ->setAttribute('size', 40)
            ->setId('regemail')
            ->setLabel($label)
            ->setCustomErrorMessage($error);


        $label = $this->getTranslator()->translate('form.password.label', 'user');
        $error = $this->getTranslator()->translate('form.password.error', 'user');
        $placeholder = $this->getTranslator()->translate('form.password.placeholder', 'user');
        $password = new Password('password');
        $password->setRequired(true)
            ->setClass('form-control password')
            ->setLabel($label)
            ->setId('regpassword')
            ->setAttribute('size', 40)
            ->setAttribute('placeholder', $placeholder)
            ->setCustomErrorMessage($error);

        $label = $this->getTranslator()->translate('form.confirm.label', 'user');
        $error = $this->getTranslator()->translate('form.confirm.error', 'user');
        $placeholder = $this->getTranslator()->translate('form.confirm.placeholder', 'user');
        $confirm = new Password('confirm');
        $confirm->setRequired(true)
            ->setLabel($label)
            ->setAttribute('size', 40)
            ->setAttribute('placeholder', $placeholder)
            ->setCustomErrorMessage($error);

        $label = $this->getTranslator()->translate('form.submit.label', 'user');
        $submit = new Submit('submit');
        $submit->setValue($label);

        $stringToLower = new StringToLower();
        $email->addFilter(new FilterAdapterZf($stringToLower));

        $renderer = new HorizontalFormRenderer();

        $this->addField($email)
            ->addField($password)
            ->addField($confirm)
            ->addField($submit)
            ->setFormRenderer($renderer);
    }

}