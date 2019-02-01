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

namespace App\IqrfNetModule\Presenters;

use App\CoreModule\Presenters\ProtectedPresenter;
use App\IqrfNetModule\Forms\OsConfigFormFactory;
use Nette\Forms\Form;

/**
 * IQMESH Network Manager - IQRF OS configuration presenter
 */
class OsConfigPresenter extends ProtectedPresenter {

	/**
	 * @var OsConfigFormFactory IQRF OS configuration form
	 * @inject
	 */
	public $form;

	/**
	 * Create IQRF OS configuration form
	 * @return Form IQRF OS configuration form
	 */
	protected function createComponentIqrfNetOsForm(): Form {
		return $this->form->create($this);
	}

}
