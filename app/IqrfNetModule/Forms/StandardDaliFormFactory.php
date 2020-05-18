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
use App\IqrfNetModule\Exceptions\DpaErrorException;
use App\IqrfNetModule\Exceptions\EmptyResponseException;
use App\IqrfNetModule\Exceptions\UserErrorException;
use App\IqrfNetModule\Models\StandardDaliManager;
use App\IqrfNetModule\Presenters\StandardPresenter;
use Nette\Application\UI\Form;
use Nette\Forms\Container;
use Nette\Forms\Controls\SubmitButton;
use Nette\Utils\JsonException;

/**
 * IQRF Standard DALI form factory
 */
class StandardDaliFormFactory {

	/**
	 * @var FormFactory Generic form factory
	 */
	private $factory;

	/**
	 * @var StandardPresenter IQRF Standard presenter
	 */
	private $presenter;

	/**
	 * @var StandardDaliManager IQRF Standard DALI manager
	 */
	private $manager;

	/**
	 * Constructor
	 * @param FormFactory $factory Generic form factory
	 * @param StandardDaliManager $manager IQRF Standard DALI manager
	 */
	public function __construct(FormFactory $factory, StandardDaliManager $manager) {
		$this->factory = $factory;
		$this->manager = $manager;
	}

	/**
	 * Creates IQRF Standard DALI form
	 * @param StandardPresenter $presenter IQRF Standard presenter
	 * @return Form IQRF Standard DALI form
	 */
	public function create(StandardPresenter $presenter): Form {
		$this->presenter = $presenter;
		$form = $this->factory->create('iqrfnet.standard.dali');
		$form->addGroup();
		$form->addInteger('address', 'address')
			->setRequired('messages.address');
		$commands = $form->addMultiplier('commands', [$this, 'createCommandMultiplier'], 1);
		$commands->addCreateButton('commands.add')
			->addClass('btn btn-success');
		$commands->addRemoveButton('commands.remove')
			->addClass('btn btn-danger');
		$form->addGroup();
		$form->addProtection('core.errors.form-timeout');
		$form->addSubmit('send', 'send')
			->setHtmlAttribute('class', 'ajax')
			->onClick[] = [$this, 'send'];
		return $form;
	}

	/**
	 * Creates DALI command form multiplier
	 * @param Container $container Container for DALI command inputs
	 */
	public function createCommandMultiplier(Container $container): void {
		$container->addInteger('command', 'command')
			->setRequired('messages.command');
	}

	/**
	 * Sends DALI commands
	 * @param SubmitButton $button Submit button
	 */
	public function send(SubmitButton $button): void {
		/**
		 * @var array<string,mixed> $values Values from the form
		 */
		$values = $button->getForm()->getValues('array');
		try {
			$commands = array_map(function (array $array): int {
				return $array['command'];
			}, $values['commands']);
			$data = $this->manager->send($values['address'], $commands);
			$this->presenter->handleDaliResponse($data);
		} catch (UserErrorException | DpaErrorException | EmptyResponseException | JsonException $e) {
			$this->presenter->flashError('iqrfnet.standard.dali.messages.sendError');
		}
	}

}
