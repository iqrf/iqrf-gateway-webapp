<?php

declare(strict_types = 1);

namespace App\Models\Mail\Senders;

use App\GatewayModule\Models\InfoManager;
use App\Models\Database\Entities\User;
use App\Models\Mail\ConfigurationManager;
use App\Models\Mail\MailerFactory;
use Nette\Bridges\ApplicationLatte\Template;
use Nette\Bridges\ApplicationLatte\TemplateFactory;
use Nette\Localization\Translator;
use Nette\Mail\FallbackMailer;
use Nette\Mail\Mailer;
use Nette\Mail\Message;

/**
 * Base e-mail sender
 */
abstract class BaseMailSender {

	/**
	 * @var ConfigurationManager SMTP configuration manager
	 */
	protected ConfigurationManager $configuration;

	/**
	 * @var InfoManager Gateway information manager
	 */
	protected InfoManager $gatewayInfo;

	/**
	 * @var MailerFactory Mailer factory
	 */
	protected MailerFactory $mailerFactory;

	/**
	 * @var TemplateFactory Template factory
	 */
	protected TemplateFactory $templateFactory;

	/**
	 * @var Translator Translator
	 */
	protected Translator $translator;

	/**
	 * Constructor
	 * @param ConfigurationManager $configuration SMTP configuration manager
	 * @param InfoManager $gatewayInfo Gareway information manager
	 * @param MailerFactory $mailerFactory Mailer factory
	 * @param TemplateFactory $templateFactory Template factory
	 * @param Translator $translator Translator
	 */
	public function __construct(ConfigurationManager $configuration, InfoManager $gatewayInfo, MailerFactory $mailerFactory, TemplateFactory $templateFactory, Translator $translator) {
		$this->configuration = $configuration;
		$this->gatewayInfo = $gatewayInfo;
		$this->mailerFactory = $mailerFactory;
		$this->templateFactory = $templateFactory;
		$this->translator = $translator;
	}

	/**
	 * Creates the mailer
	 * @return Mailer Mailer
	 */
	protected function createMailer(): Mailer {
		return $this->mailerFactory->build();
	}

	/**
	 * Creates a new e-mail message from the Latte template
	 * @param string $fileName Template filename
	 * @param array<string, mixed> $params Template params
	 * @param User|null $user Recipient
	 * @return Message E-mail message
	 */
	protected function createMessage(string $fileName, array $params = [], ?User $user = null): Message {
		$defaultParams = [
			'gatewayInfo' => $this->gatewayInfo,
			'user' => $user,
		];
		$html = $this->renderTemplate($fileName, array_merge($defaultParams, $params));
		$mail = new Message();
		$mail->setFrom($this->configuration->getFrom(), $this->translator->translate('mail_' . $this->configuration->getTheme() . '.title'));
		if ($user !== null) {
			$mail->addTo($user->getEmail(), $user->getUserName());
		}
		$mail->setHtmlBody($html, $this->getTemplateDir());
		return $mail;
	}

	/**
	 * Creates the Latte template for the e-mail
	 * @return Template Latte template for the e-mail
	 */
	protected function createTemplate(): Template {
		$template = $this->templateFactory->createTemplate();
		$latte = $template->getLatte();
		$latte->addProvider('translator', $this->translator);
		return $template;
	}

	/**
	 * Returns the theme directory
	 * @return string Theme directory
	 */
	protected function getTemplateDir(): string {
		return __DIR__ . '/templates/' . $this->configuration->getTheme();
	}

	/**
	 * Renders the Latte template for the e-mail
	 * @param string $fileName Template file name
	 * @param array<string, mixed> $params Template parameters
	 * @return string Rendered template
	 */
	protected function renderTemplate(string $fileName, array $params): string {
		return $this->createTemplate()
			->renderToString($this->getTemplateDir() . '/' . $fileName, $params);
	}

}
