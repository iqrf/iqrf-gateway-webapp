<?php

namespace App\Presenters;

use Nette;
use Nette\Application\UI\Form;


class SignPresenter extends Nette\Application\UI\Presenter
{

    protected function createComponentSignInForm() {
        $form = new Form;
        $form->addText('username', 'Username:')
            ->setRequired('Prosím vyplňte své uživatelské jméno.');
        $form->addPassword('password', 'Password:')
            ->setRequired('Prosím vyplňte své heslo.');
        $form->addSubmit('send', 'Sign in');
        $form->addProtection('Vypršel časový limit, odešlete formulář znovu');
        $form->onSuccess[] = [$this, 'signInFormSucceeded'];
        return $form;
    }

    /**
     * @param $form Nette\Application\UI\Form
     * @param $values Nette\Utils\ArrayHash
     */
    public function signInFormSucceeded($form, $values) {
        try {
            $this->getUser()->login($values->username, $values->password);
            $this->redirect('Homepage:');
        } catch (Nette\Security\AuthenticationException $e) {
            $form->addError('Nesprávné přihlašovací jméno nebo heslo.');
        }
    }

    public function actionOut() {
		    $this->getUser()->logout();
	  }
}
