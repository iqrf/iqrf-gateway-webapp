<?php

/**
 * TEST: App\CoreModule\Models\FeatureManager
 * @covers App\CoreModule\Models\FeatureManager
 * @phpVersion >= 7.1
 * @testCase
 */

declare(strict_types = 1);

namespace Tests\Integration\CoreModule\Models;

use App\CoreModule\Exceptions\FeatureNotFoundException;
use App\CoreModule\Models\FeatureManager;
use Nette\Utils\FileSystem;
use Tester\Assert;
use Tester\TestCase;

require __DIR__ . '/../../../bootstrap.php';

/**
 * Tests for optional feature manager
 */
class FeatureManagerTest extends TestCase {

	/**
	 * Path to the temporary directory
	 */
	private const TMP_PATH = __DIR__ . '/../../../../temp/tests';

	/**
	 * @var FeatureManager Optional feature manager
	 */
	private $manager;

	/**
	 * Sets up the test environment
	 */
	protected function setUp(): void {
		$original = __DIR__ . '/../../../data/features.neon';
		$path = self::TMP_PATH . '/features.neon';
		FileSystem::copy($original, $path);
		$this->manager = new FeatureManager($path);
	}

	/**
	 * Tests the constructor with nonexistent path
	 */
	public function testConstructorNonexistent(): void {
		$manager = new FeatureManager(self::TMP_PATH . '/nonsense');
		Assert::same(['docs'], $manager->listEnabled());
	}

	/**
	 * Tests the function to edit feature configuration (nonexistent feature)
	 */
	public function testEditNotFound(): void {
		Assert::exception(function (): void {
			$this->manager->edit('nonsense', ['enabled' => true]);
		}, FeatureNotFoundException::class);
	}

	/**
	 * Tests the function to get optional feature configuration
	 */
	public function testGet(): void {
		$expected = [
			'enabled' => false,
			'url' => '/grafana/',
		];
		Assert::same($expected, $this->manager->get('grafana'));
	}

	/**
	 * Tests the function to check if the feature is enabled
	 */
	public function testIsEnabled(): void {
		Assert::true($this->manager->isEnabled('docs'));
		Assert::exception(function (): void {
			$this->manager->isEnabled('nonsence');
		}, FeatureNotFoundException::class);
	}

	/**
	 * Tests the function to check if the feature has URL
	 */
	public function testHasUrl(): void {
		Assert::false($this->manager->hasUrl('networkManager'));
		Assert::true($this->manager->hasUrl('docs'));
	}

	/**
	 * Tests the function to list enabled features
	 */
	public function testListEnabled(): void {
		$expected = ['docs'];
		Assert::same($expected, $this->manager->listEnabled());
	}

	/**
	 * Tests the function to list enabled features with URLs
	 */
	public function testListUrl(): void {
		$expected = [
			'docs' => 'https://docs.iqrf.org/iqrf-gateway/',
		];
		Assert::same($expected, $this->manager->listUrl());
	}

	/**
	 * Tests the function to set feature enablement
	 */
	public function testSetEnabled(): void {
		$expected = ['docs'];
		Assert::same($expected, $this->manager->listEnabled());
		$this->manager->setEnabled(['pixla', 'ssh'], true);
		$expected = ['docs', 'pixla', 'ssh'];
		Assert::same($expected, $this->manager->listEnabled());
	}

	/**
	 * Tests the function to set feature enablement (nonexistent feature)
	 */
	public function testSetEnabledNotFound(): void {
		Assert::exception(function (): void {
			$this->manager->setEnabled(['nonsense']);
		}, FeatureNotFoundException::class);
	}

}

$test = new FeatureManagerTest();
$test->run();
