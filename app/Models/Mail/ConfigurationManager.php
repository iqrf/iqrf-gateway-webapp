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

use App\Entities\MailerConfiguration;
use App\Exceptions\InvalidSmtpConfigException;
use Nette\IOException;
use Nette\Neon\Exception as NeonException;
use Nette\Neon\Neon;
use Nette\Utils\FileSystem;
use Nette\Utils\Json;
use PHPMailer\PHPMailer\SMTP;
use Spatie\SslCertificate\Downloader;
use Spatie\SslCertificate\Exceptions\CouldNotDownloadCertificate;
use Spatie\SslCertificate\SslCertificate;

/**
 * Mailer configuration manager
 */
class ConfigurationManager {

	/**
	 * Constructor
	 * @param string $path Path to the configuration file
	 * @param array<string, mixed>|null $config Configuration
	 */
	public function __construct(
		private readonly string $path,
		private readonly ?array $config = null,
	) {
	}

	/**
	 * Returns the sender e-mail address
	 * @return string Sender e-mail address
	 */
	public function getFrom(): string {
		return $this->read()->from;
	}

	/**
	 * Returns the theme name
	 * @return string Theme name
	 */
	public function getTheme(): string {
		return $this->read()->theme;
	}

	/**
	 * Reads mailer configuration
	 * @return MailerConfiguration Mailer configuration
	 */
	public function read(): MailerConfiguration {
		if ($this->config !== null) {
			$configuration = $this->config;
		} else {
			try {
				$configuration = Neon::decodeFile($this->path) ?? [];
			} catch (IOException | NeonException) {
				$configuration = [];
			}
		}
		return MailerConfiguration::mergeDefaults($configuration);
	}

	/**
	 * Writes the mailer configuration
	 * @param MailerConfiguration $configuration Mailer configuration to write
	 * @throws IOException
	 */
	public function write(MailerConfiguration $configuration): void {
		$content = Neon::encode($configuration->jsonSerialize(), blockMode: true);
		FileSystem::write($this->path, $content);
	}

	/**
	 * Tests the mailer configuration
	 * @param MailerConfiguration $configuration Mailer configuration
	 * @thrown InvalidSmtpConfigException
	 */
	public function test(MailerConfiguration $configuration): void {
		$configuration = MailerConfiguration::mergeDefaults($configuration);
		if (!$configuration->enabled) {
			return;
		}
		$smtp = new SMTP();
		$options = [
			'ssl' => [
				'peer_name' => $configuration->host,
				'verify_peer' => true,
				'verify_peer_name' => true,
				'allow_self_signed' => false,
				'SNI_enabled' => true,
				'security_level' => 2,
			],
		];
		$host = ($configuration->secure === 'ssl' ? 'ssl://' : '') . $configuration->host;
		if ($configuration->secure === 'ssl') {
			$this->validateCertificate($configuration);
		}
		if (!$smtp->connect($host, $configuration->port, $configuration->timeout, $options)) {
			$errors = Json::encode($smtp->getError());
			throw new InvalidSmtpConfigException('Could not connect to the SMTP server. Errors: ' . $errors, InvalidSmtpConfigException::CONNECTION_FAILED, null);
		}
		if (!$smtp->hello($configuration->clientHost)) {
			$errors = Json::encode($smtp->getError());
			$smtp->close();
			throw new InvalidSmtpConfigException('Could not send EHLO/HELO to the SMTP server. Errors: ' . $errors, InvalidSmtpConfigException::HELLO_FAILED);
		}
		$extensions = $smtp->getServerExtList();
		if (!array_key_exists('STARTTLS', $extensions) && $configuration->secure === 'tls') {
			$errors = Json::encode($smtp->getError());
			$smtp->close();
			throw new InvalidSmtpConfigException('The SMTP server does not support TLS. Errors: ' . $errors, InvalidSmtpConfigException::STARTTLS_NOT_SUPPORTED);
		}
		if ($configuration->secure === 'tls') {
			if (!$smtp->startTLS()) {
				$errors = Json::encode($smtp->getError());
				$smtp->close();
				throw new InvalidSmtpConfigException('Could not use STARTTLS to connect to the SMTP server. Errors: ' . $errors, InvalidSmtpConfigException::STARTTLS_FAILED);
			}
			if (!$smtp->hello($configuration->clientHost)) {
				$errors = Json::encode($smtp->getError());
				$smtp->close();
				throw new InvalidSmtpConfigException('Could not send EHLO/HELO to the SMTP server. Errors: ' . $errors, InvalidSmtpConfigException::HELLO_FAILED);
			}
			$extensions = $smtp->getServerExtList();
		}
		if (!array_key_exists('AUTH', $extensions) && $configuration->hasCredentials()) {
			$smtp->close();
			throw new InvalidSmtpConfigException('The SMTP server does not support authentication.', InvalidSmtpConfigException::AUTH_NOT_SUPPORTED);
		}
		if (!$smtp->authenticate($configuration->username, $configuration->password)) {
			$errors = Json::encode($smtp->getError());
			$smtp->close();
			throw new InvalidSmtpConfigException('Could not authenticate to the SMTP server. Errors: ' . $errors, InvalidSmtpConfigException::AUTH_FAILED);
		}
		$smtp->close();
	}

	/**
	 * Validates the SSL certificate
	 * @param MailerConfiguration $configuration Mailer configuration
	 */
	private function validateCertificate(MailerConfiguration $configuration): void {
		try {
			$downloader = new Downloader();
			$downloader->usingSni(true);
			$downloader->withVerifyPeer(false);
			$downloader->withVerifyPeerName(false);
			$downloader->usingPort($configuration->port);
			$certificates = $downloader->getCertificates($configuration->host);
			if ($certificates === []) {
				throw new InvalidSmtpConfigException('Could not download the SSL certificate.', InvalidSmtpConfigException::SSL_CERTIFICATE_NOT_FOUND);
			}
			$certificate = $certificates[0];
			assert($certificate instanceof SslCertificate);
			if (!$certificate->appliesToUrl($configuration->host)) {
				throw new InvalidSmtpConfigException('The SSL certificate is not valid for the host. Valid hosts: ' . implode(', ', $certificate->getDomains()), InvalidSmtpConfigException::SSL_CERTIFICATE_INVALID);
			}
			if ($certificate->isExpired()) {
				throw new InvalidSmtpConfigException('The SSL certificate is expired. Expiration date: ' . $certificate->expirationDate()->toIso8601String(), InvalidSmtpConfigException::SSL_CERTIFICATE_EXPIRED);
			}
			if ($certificate->validFromDate()->isFuture()) {
				throw new InvalidSmtpConfigException('The SSL certificate is not valid yet. Valid from: ' . $certificate->validFromDate()->toIso8601String(), InvalidSmtpConfigException::SSL_CERTIFICATE_EXPIRED);
			}
		} catch (CouldNotDownloadCertificate) {
			throw new InvalidSmtpConfigException('Could not connect to the SMTP server.', InvalidSmtpConfigException::CONNECTION_FAILED, null);
		}
	}

}
