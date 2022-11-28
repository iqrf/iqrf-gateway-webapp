<?php

/**
 * Copyright 2017-2021 IQRF Tech s.r.o.
 * Copyright 2019-2021 MICRORISC s.r.o.
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

namespace App\Models\Mail\Senders;

use App\Models\Database\Entities\User;
use App\Models\Mail\ConfigurationManager;
use App\Models\Mail\MailerFactory;

/**
 * Mailer configuration test mail sender
 */
class MailerConfigurationTestMailSender extends BaseMailSender {

	/**
	 * Sends test e-mail to check mailer configuration
	 * @param User $user User
	 * @param array<string, mixed> $config Configuration
	 */
	public function send(User $user, ?array $config = null): void {
		if ($config !== null) {
			$this->configuration = new ConfigurationManager('', $config);
			$this->mailerFactory = new MailerFactory($this->configuration);
		}
		$mail = $this->createMessage('mailerConfigurationTest.latte', [], $user);
		$this->createMailer()->send($mail);
	}

}
