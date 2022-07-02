<?php

/**
 * Copyright 2017-2022 IQRF Tech s.r.o.
 * Copyright 2019-2022 MICRORISC s.r.o.
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

namespace App\CoreModule\Tracy;

use App\CoreModule\Entities\CommandStack;
use Tracy\IBarPanel;

/**
 * Command Tracy panel
 */
class CommandPanel implements IBarPanel {

	/**
	 * @var CommandStack Command stack
	 */
	protected $stack;

	/**
	 * Constructor
	 * @param CommandStack $stack Command stack
	 */
	public function __construct(CommandStack $stack) {
		$this->stack = $stack;
	}

	/**
	 * Renders HTML code for Commands tab
	 * @return string HTML code for Commands tab
	 */
	public function getTab(): string {
		if ($this->stack->getCommands() === []) {
			return '';
		}
		ob_start();
		require __DIR__ . '/templates/Command/tab.phtml';
		return (string) ob_get_clean();
	}

	/**
	 * Renders HTML code for Commands panel
	 * @return string HTML code for Commands panel
	 */
	public function getPanel(): string {
		ob_start();
		require __DIR__ . '/templates/Command/panel.phtml';
		return (string) ob_get_clean();
	}

}
