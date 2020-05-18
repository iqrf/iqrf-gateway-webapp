<?php

/**
 * Copyright 2017 MICRORISC s.r.o.
 * Copyright 2017-2019 IQRF Tech s.r.o.
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
declare(strict_types = 1);

namespace App\IqrfNetModule\Forms;

use App\CoreModule\Forms\FormFactory;
use App\IqrfNetModule\Presenters\TrConfigPresenter;
use Nette\Application\UI\Form;

class ChangeAddressFormFactory {

	/**
	 * @var FormFactory Generic form factory
	 */
	private $factory;

	/**
	 * @var TrConfigPresenter TR configuration presenter
	 */
	private $presenter;

	/**
	 * Constructor
	 * @param FormFactory $factory Generic form factory
	 */
	public function __construct(FormFactory $factory) {
		$this->factory = $factory;
	}

	/**
	 * Creates change a network device address form
	 * @param TrConfigPresenter $presenter TR configuration presenter
	 * @return Form Change a network device address
	 */
	public function create(TrConfigPresenter $presenter): Form {
		$this->presenter = $presenter;
		$form = $this->factory->create('iqrfnet.changeAddress');
		$form->addInteger('address', 'address')
			->addRule(Form::RANGE, 'messages.address', [0, 239])
			->setRequired('messages.address')
			->setDefaultValue($this->presenter->getParameter('address') ?? 0);
		$form->addSubmit('read', 'read');
		$form->addProtection('core.errors.form-timeout');
		$form->onSuccess[] = [$this, 'onSuccess'];
		return $form;
	}

	/**
	 * Redirects on success
	 * @param Form $form Change a network device address form
	 */
	public function onSuccess(Form $form): void {
		$this->presenter->redirect('this', ['address' => $form->getValues()->address]);
	}

}
