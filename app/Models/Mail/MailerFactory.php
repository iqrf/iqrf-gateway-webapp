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

namespace App\Models\Mail;

use Nette\Mail\FallbackMailer;
use Nette\Mail\SendmailMailer;
use Nette\Mail\SmtpMailer;

/**
 * Mailer factory
 */
class MailerFactory {

	/**
	 * @var ConfigurationManager Mailer configuration manager
	 */
	private $configuration;

	/**
	 * Constructor
	 * @param ConfigurationManager $configuration Mailer configuration manager
	 */
	public function __construct(ConfigurationManager $configuration) {
		$this->configuration = $configuration;
	}

	/**
	 * Creates the mailer
	 * @return FallbackMailer Fallback mailer
	 */
	public function build(): FallbackMailer {
		$mailers = [new SendmailMailer()];
		$smtpMailer = $this->buildSmtpMailer();
		if ($smtpMailer !== null) {
			$mailers[] = $smtpMailer;
		}
		return new FallbackMailer($mailers);
	}

	/**
	 * Creates SMTP mailer
	 * @return SmtpMailer|null SMTP mailer
	 */
	private function buildSmtpMailer(): ?SmtpMailer {
		$configuration = $this->configuration->read();
		if ($configuration['enabled'] !== true) {
			return null;
		}
		return new SmtpMailer($configuration);
	}

}
