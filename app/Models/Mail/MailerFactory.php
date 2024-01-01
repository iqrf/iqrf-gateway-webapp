<?php

/**
 * Copyright 2017-2024 IQRF Tech s.r.o.
 * Copyright 2019-2024 MICRORISC s.r.o.
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

namespace App\Models\Mail;

use Nette\Mail\Mailer;
use Nette\Mail\SendmailMailer;
use Nette\Mail\SmtpMailer;

/**
 * Mailer factory
 */
class MailerFactory {

	/**
	 * Constructor
	 * @param ConfigurationManager $configuration Mailer configuration manager
	 */
	public function __construct(
		private readonly ConfigurationManager $configuration,
	) {
	}

	/**
	 * Creates the mailer
	 * @return Mailer Fallback mailer
	 */
	public function build(): Mailer {
		$smtpMailer = $this->buildSmtpMailer();
		if ($smtpMailer !== null) {
			return $smtpMailer;
		}
		return new SendmailMailer();
	}

	/**
	 * Creates SMTP mailer
	 * @return SmtpMailer|null SMTP mailer
	 */
	private function buildSmtpMailer(): ?SmtpMailer {
		$configuration = $this->configuration->read();
		if (!$configuration->enabled) {
			return null;
		}
		return new SmtpMailer(
			$configuration->host,
			$configuration->username,
			$configuration->password,
			$configuration->port,
			$configuration->secure,
			$configuration->persistent,
			$configuration->timeout,
			$configuration->clientHost,
			is_array($configuration->context) ? $configuration->context : null,
		);
	}

}
