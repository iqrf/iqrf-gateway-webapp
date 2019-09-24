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

namespace App\NetworkModule\Forms;

use App\CoreModule\Forms\FormFactory;
use App\NetworkModule\Entities\ConnectionDetail;
use App\NetworkModule\Enums\IPv4Methods;
use App\NetworkModule\Enums\IPv6Methods;
use App\NetworkModule\Models\ConnectionManager;
use App\NetworkModule\Presenters\EthernetPresenter;
use Nette\Application\UI\Form;
use Nette\Forms\Container;
use Nette\SmartObject;
use Ramsey\Uuid\Uuid;

/**
 * Ethernet network configuration form factory
 */
class EthernetFormFactory {

	use SmartObject;

	/**
	 * @var ConnectionDetail Detailed network connection entity
	 */
	private $connection;

	/**
	 * @var FormFactory Generic form factory
	 */
	private $factory;

	/**
	 * @var EthernetPresenter Ethernet network configuration presenter
	 */
	private $presenter;

	/**
	 * @var ConnectionManager Network connection manager
	 */
	private $manager;

	/**
	 * Ethernet network configuration form constructor
	 * @param FormFactory $factory Generic form factory
	 * @param ConnectionManager $manager Network connection manager
	 */
	public function __construct(FormFactory $factory, ConnectionManager $manager) {
		$this->factory = $factory;
		$this->manager = $manager;
	}

	/**
	 * Creates the Ethernet network configuration form
	 * @param EthernetPresenter $presenter Ethernet network configuration presenter
	 * @return Form Ethernet network configuration form
	 */
	public function create(EthernetPresenter $presenter): Form {
		$this->presenter = $presenter;
		$uuid = Uuid::fromString($this->presenter->getParameter('uuid'));
		$this->connection = $this->manager->get($uuid);
		$form = $this->factory->create('network.ethernet.form');
		$this->createIpv4($form);
		$this->createIpv6($form);
		$form->setDefaults($this->connection->toForm());
		$form->addGroup();
		$form->addSubmit('save', 'save');
		$form->onSuccess[] = [$this, 'save'];
		return $form;
	}

	/**
	 * Creates IPv6 connection form controls
	 * @param Form $form Network connection configuration form
	 */
	private function createIpv4(Form &$form): void {
		$form->addGroup('ipv4.title');
		$ipv4 = $form->addContainer('ipv4');
		$ipv4->addSelect('method', 'ipv4.method')
			->setPrompt('ipv4.prompts.method')
			->setRequired('ipv4.messages.method')
			->setItems($this->getIpv4Methods())
			->setDisabled(['shared']);
		$addresses = $ipv4->addMultiplier('addresses', [$this, 'createIPv4AddressMultiplier'], 1);
		$addresses->addCreateButton('ipv4.addresses.add')
			->addClass('btn btn-success');
		$addresses->addRemoveButton('ipv4.addresses.remove')
			->addClass('btn btn-danger');
		$ipv4->addText('gateway', 'ipv4.gateway');
		$dns = $ipv4->addMultiplier('dns', [$this, 'createIPv4DnsMultiplier'], 1);
		$dns->addCreateButton('ipv4.dns.add')
			->addClass('btn btn-success');
		$dns->addRemoveButton('ipv4.dns.remove')
			->addClass('btn btn-danger');
	}

	/**
	 * Creates IPv4 address configuration form multiplier
	 * @param Container $container Container for IPv4 address form controls
	 * @param Form $form Network connection configuration form
	 */
	public function createIpv4AddressMultiplier(Container $container, Form $form): void {
		$container->addText('address', 'ipv4.address')
			->addConditionOn($form['ipv4-method'], Form::EQUAL, 'manual')
			->setRequired('ipv4.messages.address');
		$container->addText('mask', 'ipv4.mask')
			->addConditionOn($form['ipv4-method'], Form::EQUAL, 'manual')
			->setRequired('ipv4.messages.mask');
	}

	/**
	 * Creates IPv4 DNS configuration form multiplier
	 * @param Container $container Container for IPv4 DNS form controls
	 */
	public function createIpv4DnsMultiplier(Container $container): void {
		$container->addText('address', 'ipv4.dns.address');
	}

	/**
	 * Creates IPv6 connection form controls
	 * @param Form $form Network connection configuration form
	 */
	private function createIpv6(Form &$form): void {
		$form->addGroup('ipv6.title');
		$ipv6 = $form->addContainer('ipv6');
		$ipv6->addSelect('method', 'ipv6.method')
			->setPrompt('ipv6.prompts.method')
			->setRequired('ipv6.messages.method')
			->setItems($this->getIpv6Methods())
			->setDisabled(['disabled', 'ignore', 'shared']);
		$addresses = $ipv6->addMultiplier('addresses', [$this, 'createIPv6AddressMultiplier'], 1);
		$addresses->addCreateButton('ipv6.addresses.add')
			->addClass('btn btn-success');
		$addresses->addRemoveButton('ipv6.addresses.remove')
			->addClass('btn btn-danger');
		$dns = $ipv6->addMultiplier('dns', [$this, 'createIPv6DnsMultiplier'], 1);
		$dns->addCreateButton('ipv6.dns.add')
			->addClass('btn btn-success');
		$dns->addRemoveButton('ipv6.dns.remove')
			->addClass('btn btn-danger');
	}

	/**
	 * Creates IPv6 address configuration form multiplier
	 * @param Container $container Container for IPv6 address form controls
	 * @param Form $form Network connection configuration form
	 */
	public function createIpv6AddressMultiplier(Container $container, Form $form): void {
		$container->addText('address', 'ipv6.address')
			->addConditionOn($form['ipv6-method'], Form::EQUAL, 'manual')
			->setRequired('ipv6.messages.address');
		$container->addInteger('prefix', 'ipv6.prefix')
			->addConditionOn($form['ipv6-method'], Form::EQUAL, 'manual')
			->setRequired('ipv6.messages.prefix');
		$container->addText('gateway', 'ipv6.gateway');
	}

	/**
	 * Creates IPv6 DNS configuration form multiplier
	 * @param Container $container Container for IPv6 DNS form controls
	 */
	public function createIpv6DnsMultiplier(Container $container): void {
		$container->addText('address', 'ipv6.dns.address');
	}

	/**
	 * Returns the IPv4 connection methods
	 * @return array<string,string> IPv4 connection methods
	 */
	private function getIpv4Methods(): array {
		$methods = [];
		foreach (IPv4Methods::getAvailableValues() as $method) {
			$methods[$method->toScalar()] = 'ipv4.methods.' . $method->toScalar();
		}
		return $methods;
	}

	/**
	 * Returns the IPv6 connection methods
	 * @return array<string,string> IPv6 connection methods
	 */
	private function getIpv6Methods(): array {
		$methods = [];
		foreach (IPv6Methods::getAvailableValues() as $method) {
			$methods[$method->toScalar()] = 'ipv6.methods.' . $method->toScalar();
		}
		return $methods;
	}

	/**
	 * Saves the Ethernet network connection form
	 * @param Form $form Ethernet network connection form
	 */
	public function save(Form $form): void {
		$this->manager->set($this->connection, $form->getValues());
		$this->presenter->flashSuccess('network.ethernet.form.messages.success');
		$this->presenter->redirect('Ethernet:default');
	}

}
