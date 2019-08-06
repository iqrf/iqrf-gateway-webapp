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

namespace App\CoreModule\Traits;

use App\CoreModule\Presenters\BasePresenter;
use stdClass;

/**
 * Trait for flash messages in presenters
 * @mixin BasePresenter
 */
trait TPresenterFlashMessage {

	/**
	 * Saves the info flash message to template, that can be displayed after redirect or AJAX request
	 * @param string $message Message
	 * @return stdClass Flash message object
	 */
	public function flashInfo(string $message): stdClass {
		return $this->flashMessage($message, 'info');
	}

	/**
	 * Saves the flash message to template, that can be displayed after redirect or AJAX request
	 * @param string $message Message
	 * @param string $type Message's type
	 * @return stdClass Flash message object
	 * @phpcsSuppress SlevomatCodingStandard.TypeHints.TypeHintDeclaration.MissingParameterTypeHint
	 * @internal
	 */
	public function flashMessage($message, string $type = 'info'): stdClass {
		if ($this->isAjax()) {
			$this->redrawControl('flashes');
		}
		return parent::flashMessage($message, $type);
	}

	/**
	 * Saves the success flash message to template, that can be displayed after redirect or AJAX request
	 * @param string $message Message
	 * @return stdClass Flash message object
	 */
	public function flashSuccess(string $message): stdClass {
		return $this->flashMessage($message, 'success');
	}

	/**
	 * Saves the warning flash message to template, that can be displayed after redirect or AJAX request
	 * @param string $message Message
	 * @return stdClass Flash message object
	 */
	public function flashWarning(string $message): stdClass {
		return $this->flashMessage($message, 'warning');
	}

	/**
	 * Saves the error flash message to template, that can be displayed after redirect or AJAX request
	 * @param string $message Message
	 * @return stdClass Flash message object
	 */
	public function flashError(string $message): stdClass {
		return $this->flashMessage($message, 'danger');
	}

}
