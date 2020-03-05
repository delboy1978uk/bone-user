<?php

namespace Bone\User\Form;

use Bone\Form;
use Del\Form\Field\Submit;
use Del\Form\Field\Text\EmailAddress;
use Del\Form\Field\Text\Password;
use Del\Form\Filter\Adapter\FilterAdapterZf;
use Del\Form\Renderer\HorizontalFormRenderer;
use Laminas\Filter\StringToLower;

class LoginForm extends Form
{
    public function init()
    {
        $translator= $this->getTranslator();

        $email = new EmailAddress('email');
        $email->setRequired(true);
        $email->setAttribute('size', 40);
        $email->setId('regemail');
        $email->setLabel($translator->translate('form.email.label', 'user'));
        $email->setCustomErrorMessage($translator->translate('form.email.error', 'user'));

        $password = new Password('password');
        $password->setRequired(true);
        $password->setClass('form-control password');
        $password->setLabel($translator->translate('form.password.label', 'user'));
        $password->setId('regpassword');
        $password->setAttribute('size', 40);
        $password->setAttribute('placeholder', $translator->translate('form.password.placeholder', 'user'));
        $password->setCustomErrorMessage($translator->translate('form.password.error', 'user'));

        $submit = new Submit('submit');
        $submit->setValue($translator->translate('user.login', 'user'));

        $stringToLower = new StringToLower();
        $email->addFilter(new FilterAdapterZf($stringToLower));

        $renderer = new HorizontalFormRenderer();

        $this->addField($email);
        $this->addField($password);
        $this->addField($submit);
        $this->setFormRenderer($renderer);
    }

}