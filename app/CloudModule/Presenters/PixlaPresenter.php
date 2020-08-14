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

namespace App\CloudModule\Presenters;

use App\CloudModule\Forms\PixlaFormFactory;
use App\CloudModule\Models\PixlaManager;
use App\CoreModule\Presenters\ProtectedPresenter;
use App\ServiceModule\Exceptions\NonexistentServiceException;
use Nette\Application\UI\Form;

/**
 * PIXLA management system presenter
 */
class PixlaPresenter extends ProtectedPresenter {

	/**
	 * @var PixlaFormFactory Generic form factory
	 * @inject
	 */
	public $formFactory;

	/**
	 * @var PixlaManager PIXLA management system manager
	 */
	private $manager;

	/**
	 * Constructor
	 * @param PixlaManager $manager PIXLa management system manager
	 */
	public function __construct(PixlaManager $manager) {
		$this->manager = $manager;
		parent::__construct();
	}

	/**
	 * Disables and stops PIXLA client
	 */
	public function handleDisable(): void {
		$this->manager->disableService();
		$this->flashSuccess('cloud.pixla.messages.disable');
		if (!$this->isAjax()) {
			$this->setView('default');
			$this->redirect('Pixla:default');
		}
	}

	/**
	 * Enables and starts PIXLA client
	 */
	public function handleEnable(): void {
		$this->manager->enableService();
		$this->flashSuccess('cloud.pixla.messages.enable');
		if (!$this->isAjax()) {
			$this->setView('default');
			$this->redirect('Pixla:default');
		}
	}

	/**
	 * Renders the default page
	 */
	public function renderDefault(): void {
		try {
			$this->template->status = $this->manager->getServiceStatus() ? 'enabled' : 'disabled';
		} catch (NonexistentServiceException $e) {
			$this->template->status = 'missing';
		}
		$this->template->token = $this->manager->getToken();
		if ($this->isAjax()) {
			$this->presenter->redrawControl('pixla');
		}
	}

	/**
	 * Checks if the PIXLA manager is enabled
	 */
	protected function startup(): void {
		parent::startup();
		if (!$this->featureManager->isEnabled('pixla')) {
			$this->flashError('cloud.pixla.messages.disabled');
			$this->redirect('Homepage:default');
		}
	}

	/**
	 * Creates the PIXLA form
	 * @return Form PIXLA form
	 */
	protected function createComponentPixlaForm(): Form {
		return $this->formFactory->create($this);
	}

}
