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

use App\Models\Database\Entities\PasswordRecovery;
use Nette\Bridges\ApplicationLatte\Template;
use Nette\Mail\Message;

class PasswordRecoveryMailSender extends BaseMailSender {

	/**
	 * Sends forgotten password recovery e-mail
	 * @param PasswordRecovery $recovery Forgotten password recovery request
	 * @param string $baseUrl Base URL
	 */
	public function send(PasswordRecovery $recovery, string $baseUrl = ''): void {
		$template = $this->createTemplate();
		assert($template instanceof Template);
		$user = $recovery->getUser();
		$params = [
			'hostname' => gethostname(),
			'url' => $baseUrl . '/account/recovery/' . $recovery->getUuid(),
			'user' => $user,
		];
		$html = $template->renderToString(__DIR__ . '/templates/passwordRecovery.latte', $params);
		$mail = new Message();
		$mail->setFrom('iqrf@romanondracek.cz', $this->translator->translate('core.title'));
		$mail->addTo($user->getEmail(), $user->getUserName());
		$mail->setSubject($this->translator->translate('mail.passwordRecovery.title'));
		$mail->setHtmlBody($html);
		$this->createMailer()->send($mail);
	}

}