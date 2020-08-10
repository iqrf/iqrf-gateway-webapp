<?php

/**
 * TEST: App\IqrfNetModule\Entities\DeviceStatus
 * @covers App\IqrfNetModule\Entities\DeviceStatus
 * @phpVersion >= 7.2
 * @testCase
 */
declare(strict_types = 1);

namespace Tests\Unit\IqrfNetModule\Entities;

use App\IqrfNetModule\Entities\DeviceStatus;
use App\IqrfNetModule\Enums\DeviceTypes;
use Tester\Assert;
use Tester\TestCase;

require __DIR__ . '/../../../bootstrap.php';

/**
 * Tests for device status entity
 */
final class DeviceStatusTest extends TestCase {

	/**
	 * @var DeviceStatus Device status entity
	 */
	private $entity;

	/**
	 * Device status icons
	 */
	private const ICONS = [
		DeviceTypes::NONE => '<span class=\'glyphicon glyphicon-remove text-danger\'></span>',
		DeviceTypes::COORDINATOR => '<span class=\'glyphicon glyphicon-home text-info\'></span>',
		DeviceTypes::BONDED => '<span class=\'glyphicon glyphicon-ok text-primary\'></span>',
		DeviceTypes::DISCOVERED => '<span class=\'glyphicon glyphicon-signal text-primary\'></span>',
		DeviceTypes::BONDED_ONLINE => '<span class=\'glyphicon glyphicon-ok text-success\'></span>',
		DeviceTypes::DISCOVERED_ONLINE => '<span class=\'glyphicon glyphicon-signal text-success\'></span>',
	];

	/**
	 * Sets up the test environment
	 */
	protected function setUp(): void {
		$this->entity = new DeviceStatus(0);
	}

	/**
	 * Tests the function to get device network address
	 */
	public function testGetAddress(): void {
		Assert::same(0, $this->entity->getAddress());
	}

	/**
	 * Tests the function to get device status icon
	 */
	public function testGetIcon(): void {
		foreach (self::ICONS as $type => $icon) {
			$this->entity->setType($type);
			Assert::same($icon, $this->entity->getIcon());
		}
	}

	/**
	 * Tests the function to get device type
	 */
	public function testGetType(): void {
		Assert::same(DeviceTypes::NONE, $this->entity->getType());
	}

	/**
	 * Tests the function to set device type
	 */
	public function testSetType(): void {
		$this->entity->setType(DeviceTypes::DISCOVERED);
		Assert::same(DeviceTypes::DISCOVERED, $this->entity->getType());
		$this->entity->setType(DeviceTypes::ONLINE);
		Assert::same(DeviceTypes::DISCOVERED_ONLINE, $this->entity->getType());
	}

}

$test = new DeviceStatusTest();
$test->run();
