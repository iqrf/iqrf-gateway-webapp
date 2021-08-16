<?php

declare(strict_types = 1);

namespace App\Models\Mail;

use Nette\Application\UI\Template;
use Nette\Bridges\ApplicationLatte\TemplateFactory;
use Nette\Localization\Translator;
use Nette\Mail\FallbackMailer;

/**
 * Base e-mail sender
 */
abstract class BaseMailSender {

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
	 * @param MailerFactory $mailerFactory Mailer factory
	 * @param TemplateFactory $templateFactory Template factory
	 * @param Translator $translator Translator
	 */
	public function __construct(MailerFactory $mailerFactory, TemplateFactory $templateFactory, Translator $translator) {
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
		$latte->addFilter('nl2br', 'nl2br');
		$latte->addProvider('translator', $this->translator);
		return $template;
	}

}
