<?php

namespace Bone\User\Form;

use Del\Form\AbstractForm;
use Del\Form\Field\Submit;
use Del\Form\Field\Text\EmailAddress;
use Del\Form\Field\Text\Password;
use Del\Form\Filter\Adapter\FilterAdapterZf;
use Del\Form\Renderer\HorizontalFormRenderer;
use Laminas\Filter\StringToLower;

class ResetPasswordForm extends AbstractForm
{
    public function init()
    {
        $password = new Password('password');
        $password->setRequired(true);
        $password->setClass('form-control password');
        $password->setLabel('Password');
        $password->setId('password');
        $password->setAttribute('size', 40);
        $password->setAttribute('placeholder', 'Enter a password');
        $password->setCustomErrorMessage('You must input a password.');

        $confirm = new Password('confirm');
        $confirm->setRequired(true);
        $confirm->setLabel('Confirm Password');
        $confirm->setAttribute('size', 40);
        $confirm->setAttribute('placeholder', 'Retype your password');
        $confirm->setCustomErrorMessage('You must retype your password.');

        $submit = new Submit('submit');
        $submit->setValue('Reset Password');

        $this->addField($password);
        $this->addField($confirm);
        $this->addField($submit);
    }

}