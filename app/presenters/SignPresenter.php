<?php

/**
 * Copyright 2017 MICRORISC s.r.o.
 * 
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 * 
 *     http://www.apache.org/licenses/LICENSE-2.0
 * 
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

namespace App\Presenters;

use Nette;
use Nette\Application\UI\Form;

/**
 * Sign in/out presenter
 */
class SignPresenter extends Nette\Application\UI\Presenter {

	/**
	 * Create sign in form
	 * @return Form Sign in form
	 */
	protected function createComponentSignInForm() {
		$form = new Form;
		$form->addText('username', 'Username:')->setRequired('Prosím vyplňte své uživatelské jméno.');
		$form->addPassword('password', 'Password:')->setRequired('Prosím vyplňte své heslo.');
		$form->addSubmit('send', 'Sign in');
		$form->addProtection('Vypršel časový limit, odešlete formulář znovu');
		$form->onSuccess[] = [$this, 'signInFormSucceeded'];
		return $form;
	}

	/**
	 * Successed sended sign in form handler
	 * @param type $form Nette\Application\UI\Form Sign in form
	 * @param type $values Nette\Utils\ArrayHash Values form form
	 */
	public function signInFormSucceeded($form, $values) {
		try {
			$this->getUser()->login($values->username, $values->password);
			$this->redirect('Homepage:');
		} catch (Nette\Security\AuthenticationException $e) {
			$form->addError('Nesprávné přihlašovací jméno nebo heslo.');
		}
	}

	/**
	 * User sign out
	 */
	public function actionOut() {
		$this->getUser()->logout();
	}

}
