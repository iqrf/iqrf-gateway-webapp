<?php

/**
 * TEST: App\IqrfNetModule\Requests\ApiRequest
 * @covers App\IqrfNetModule\Requests\ApiRequest
 * @phpVersion >= 7.2
 * @testCase
 */
declare(strict_types = 1);

namespace Tests\Unit\IqrfNetModule\Requests;

use App\CoreModule\Exceptions\InvalidJsonException;
use App\IqrfNetModule\Models\MessageIdManager;
use App\IqrfNetModule\Requests\ApiRequest;
use Mockery;
use Nette\Utils\Json;
use Tester\Assert;
use Tester\TestCase;

require __DIR__ . '/../../../bootstrap.php';

/**
 * Tests for JSON API request manager
 */
final class ApiRequestTest extends TestCase {

	/**
	 * IQRF JSON API request in an array
	 */
	private const REQUEST = [
		'mType' => 'mngDaemon_Mode',
		'data' => [
			'req' => ['operMode' => 'service'],
			'returnVerbose' => true,
		],
	];

	/**
	 * @var ApiRequest JSON API Request
	 */
	private $request;

	/**
	 * Starts up test environment
	 */
	protected function setUp(): void {
		$msgIdManager = Mockery::mock(MessageIdManager::class);
		$msgIdManager->shouldReceive('generate')->andReturn('1');
		$this->request = new ApiRequest($msgIdManager);
	}

	/**
	 * Tests the function to set the request (valid request)
	 */
	public function testSetValid(): void {
		Assert::noError(function (): void {
			$this->request->set(self::REQUEST);
		});
	}

	/**
	 * Tests the function to set the request (invalid request)
	 */
	public function testSetInvalid(): void {
		Assert::exception(function (): void {
			$this->request->set(null);
		}, InvalidJsonException::class);
	}

	/**
	 * Tests the function to get the request as array
	 */
	public function testGet(): void {
		$this->request->set(self::REQUEST);
		$expected = self::REQUEST;
		$expected['data']['msgId'] = '1';
		Assert::equal($expected, $this->request->get());
	}

	/**
	 * Tests the function to get the request as JSON string
	 */
	public function testToJson(): void {
		$this->request->set(self::REQUEST);
		$array = self::REQUEST;
		$array['data']['msgId'] = '1';
		$expected = Json::encode($array, Json::PRETTY);
		Assert::equal($expected, $this->request->toJson(true));
	}

}

$test = new ApiRequestTest();
$test->run();
