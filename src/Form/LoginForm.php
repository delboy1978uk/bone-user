<?php

namespace BoneMvc\Module\BoneMvcUser\Form;

use Del\Form\AbstractForm;
use Del\Form\Field\Submit;
use Del\Form\Field\Text\EmailAddress;
use Del\Form\Field\Text\Password;
use Del\Form\Filter\Adapter\FilterAdapterZf;
use Del\Form\Renderer\HorizontalFormRenderer;
use Zend\Filter\StringToLower;

class LoginForm extends AbstractForm
{
    public function init()
    {
        $email = new EmailAddress('email');
        $email->setRequired(true);
        $email->setAttribute('size', 40);
        $email->setId('regemail');
        $email->setLabel('Email');
        $email->setCustomErrorMessage('You must input a valid email address.');

        $password = new Password('password');
        $password->setRequired(true);
        $password->setClass('form-control password');
        $password->setLabel('Password');
        $password->setId('regpassword');
        $password->setAttribute('size', 40);
        $password->setAttribute('placeholder', 'Enter a password');
        $password->setCustomErrorMessage('You must input a password.');

        $submit = new Submit('submit');
        $submit->setValue('Login');

        $stringToLower = new StringToLower();
        $email->addFilter(new FilterAdapterZf($stringToLower));

        $renderer = new HorizontalFormRenderer();

        $this->addField($email);
        $this->addField($password);
        $this->addField($submit);
        $this->setFormRenderer($renderer);
    }

}