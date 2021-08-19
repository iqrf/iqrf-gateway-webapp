<?php

declare(strict_types = 1);

namespace App\Models\Mail\Senders;

use App\Models\Mail\ConfigurationManager;
use App\Models\Mail\MailerFactory;
use Nette\Application\UI\Template;
use Nette\Bridges\ApplicationLatte\TemplateFactory;
use Nette\Localization\Translator;
use Nette\Mail\FallbackMailer;

/**
 * Base e-mail sender
 */
abstract class BaseMailSender {

	/**
	 * @var ConfigurationManager SMTP configuration manager
	 */
	protected $configuration;

	/**
	 * @var MailerFactory Mailer factory
	 */
	private $mailerFactory;

	/**
	 * @var TemplateFactory Template factory
	 */
	protected $templateFactory;

	/**
	 * @var Translator Translator
	 */
	protected $translator;

	/**
	 * Constructor
	 * @param ConfigurationManager $configuration SMTP configuration manager
	 * @param MailerFactory $mailerFactory Mailer factory
	 * @param TemplateFactory $templateFactory Template factory
	 * @param Translator $translator Translator
	 */
	public function __construct(ConfigurationManager $configuration, MailerFactory $mailerFactory, TemplateFactory $templateFactory, Translator $translator) {
		$this->configuration = $configuration;
		$this->mailerFactory = $mailerFactory;
		$this->templateFactory = $templateFactory;
		$this->translator = $translator;
	}

	/**
	 * Creates the mailer
	 * @return FallbackMailer Mailer
	 */
	protected function createMailer(): FallbackMailer {
		return $this->mailerFactory->build();
	}

	/**
	 * Creates the Latte template for the email
	 * @return Template Latte template for the email
	 */
	protected function createTemplate(): Template {
		$template = $this->templateFactory->createTemplate();
		$latte = $template->getLatte();
		$latte->addProvider('translator', $this->translator);
		return $template;
	}

}
