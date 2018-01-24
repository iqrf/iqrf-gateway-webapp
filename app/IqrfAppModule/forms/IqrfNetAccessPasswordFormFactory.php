<?php

/**
 * Copyright 2017 IQRF Tech s.r.o.
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
declare(strict_types=1);

namespace App\IqrfAppModule\Forms;

use App\Forms\FormFactory;
use App\IqrfAppModule\Model\IqrfNetManager;
use Nette;
use Nette\Application\UI\Form;
use Nette\Forms\Controls\SubmitButton;

/**
 * IQMESH Bonding form factory.
 */
class IqrfNetAccessPasswordFormFactory {

	use Nette\SmartObject;

	/**
	 * @var IqrfNetManager IQMESH Network manager
	 */
	private $manager;

	/**
	 * @var FormFactory Generic form factory
	 */
	private $factory;

	/**
	 * Constructor
	 * @param FormFactory $factory Generic form factory
	 * @param IqrfNetManager $manager IQMESH Network manager
	 */
	public function __construct(FormFactory $factory, IqrfNetManager $manager) {
		$this->factory = $factory;
		$this->manager = $manager;
	}

	/**
	 * Create IQMESH bonding form
	 * @return Form IQMESH bonding form
	 */
	public function create(): Form {
		$form = $this->factory->create();
		$form->addSelect('inputformat', 'Input format', [
        'ASCII' => 'ASCII',
        'HEX' => 'HEX',
    ]);
    $form['inputformat']->setDefaultValue('ASCII');
		$form->addText('apassword', 'Password')
         ->setRequired(false)
         ->addConditionOn($form['inputformat'], Form::EQUAL, 'ASCII')
            ->addRule(Form::MAX_LENGTH,'It has to have maximal length of 16 chars.', 16)
         ->elseCondition($form['inputformat'], Form::EQUAL, 'HEX')   
            ->addRule(Form::PATTERN, 'It has to contain hexadecimal number', '[0-9A-Fa-f]{1,2}')
            ->addRule(Form::MAX_LENGTH,'It has to have maximal length of 32 chars.', 32) ;
		$form->addSubmit('setaccesspassword', 'set Access Password')->onClick[] = [$this, 'accessPasword'];
		$form->addProtection('Timeout expired, resubmit the form.');
		return $form;
	}

	/**
	 * Bond new node
	 * @param SubmitButton $button Submit button for bonding
	 */
	public function accessPasword(SubmitButton $button) {
		$values = $button->getForm()->getValues();
		$this->manager->accessPasword($values['apassword']);
	}



}
