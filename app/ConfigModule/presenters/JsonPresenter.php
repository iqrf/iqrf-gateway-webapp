<?php

/**
 * Copyright 2018 IQRF Tech s.r.o.
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

namespace App\ConfigModule\Presenters;

use App\ConfigModule\Forms\ConfigJsonSplitterFormFactory;
use App\ConfigModule\Forms\ConfigJsonDpaApiRawFormFactory;
use App\Presenters\BasePresenter;
use Nette\Application\UI\Form;

class JsonPresenter extends BasePresenter {

	/**
	 * @var ConfigJsonSplitterFormFactory JSON Splitter form factory
	 * @inject
	 */
	public $splitterFactory;

	/**
	 * @var ConfigJsonDpaApiRawFormFactory JSON DPA API Raw form factory
	 * @inject
	 */
	public $dpaApiRawFactory;

	/**
	 * Render JSON configurator
	 */
	public function renderDefault() {
		$this->onlyForAdmins();
	}

	/**
	 * Create JSON DPA API Raw settings form
	 * @return Form JSON DPA API Raw settings form
	 */
	protected function createComponentConfigJsonSplitterForm(): Form {
		$this->onlyForAdmins();
		return $this->splitterFactory->create($this);
	}

	/**
	 * Create JSON splitter settings form
	 * @return Form JSON splitter settings form
	 */
	protected function createComponentConfigJsonDpaApiRawForm(): Form {
		$this->onlyForAdmins();
		return $this->dpaApiRawFactory->create($this);
	}

}
