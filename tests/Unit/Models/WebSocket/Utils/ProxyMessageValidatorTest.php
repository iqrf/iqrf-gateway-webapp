<?php

/**
 * TEST: App\Unit\Models\WebSocket\ProxyMessageValidator
 * @covers App\Unit\Models\WebSocket\ProxyMessageValidator
 * @phpVersion >= 8.2
 * @testCase
 */
/**
 * Copyright 2017-2025 IQRF Tech s.r.o.
 * Copyright 2019-2025 MICRORISC s.r.o.
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

namespace Tests\Unit\Models\WebSocket;

use App\Models\WebSocket\Enums\ProxyMessageType;
use App\Models\WebSocket\Utils\ProxyMessageValidator;
use Tester\Assert;
use Tester\TestCase;

require __DIR__ . '/../../../../bootstrap.php';

/**
 * Tests for ProxyMessageValidator utility class
 */
final class ProxyMessageValidatorTest extends TestCase {

	/**
	 * Timestamp
	 */
	private const TIMESTAMP = 1767261600;

	/**
	 * Access token
	 */
	private const ACCESS_TOKEN = '';

	/**
	 * Tests the function to check if message is proxy session refresh message
	 */
	public function testIsProxySessionRefreshMessageValid(): void {
		$msg = (object) [
			'type' => ProxyMessageType::PROXY_SESSION_REFRESH->value,
			'timestamp' => self::TIMESTAMP,
			'data' => (object) [
				'sessionId' => 1,
				'token' => self::ACCESS_TOKEN,
			],
		];
		Assert::true(ProxyMessageValidator::isProxySessionRefreshMessage($msg));
	}

	/**
	 * Tests the function to check if message is proxy session refresh message (missing type)
	 */
	public function testIsProxySessionRefreshMessageMissingType(): void {
		$msg = (object) [
			'timestamp' => self::TIMESTAMP,
			'data' => (object) [
				'sessionId' => 1,
				'token' => self::ACCESS_TOKEN,
			],
		];
		Assert::false(ProxyMessageValidator::isProxySessionRefreshMessage($msg));
	}

	/**
	 * Tests the function to check if message is proxy session refresh message (invalid type)
	 */
	public function testIsProxySessionRefreshMessageInvalidType(): void {
		$msg = (object) [
			'type' => 1,
			'timestamp' => self::TIMESTAMP,
			'data' => (object) [
				'sessionId' => 1,
				'token' => self::ACCESS_TOKEN,
			],
		];
		Assert::false(ProxyMessageValidator::isProxySessionRefreshMessage($msg));
	}

	/**
	 * Tests the function to check if message is proxy session refresh message (missing timestamp)
	 */
	public function testIsProxySessionRefreshMessageMissingTimestamp(): void {
		$msg = (object) [
			'type' => ProxyMessageType::PROXY_SESSION_REFRESH->value,
			'data' => (object) [
				'sessionId' => 1,
				'token' => self::ACCESS_TOKEN,
			],
		];
		Assert::false(ProxyMessageValidator::isProxySessionRefreshMessage($msg));
	}

	/**
	 * Tests the function to check if message is proxy session refresh message (invalid timestamp)
	 */
	public function testIsProxySessionRefreshMessageInvalidTimestamp(): void {
		$msg = (object) [
			'type' => ProxyMessageType::PROXY_SESSION_REFRESH->value,
			'timestamp' => strval(self::TIMESTAMP),
			'data' => (object) [
				'sessionId' => 1,
				'token' => self::ACCESS_TOKEN,
			],
		];
		Assert::false(ProxyMessageValidator::isProxySessionRefreshMessage($msg));
	}

	/**
	 * Tests the function to check if message is proxy session refresh message (missing data)
	 */
	public function testIsProxySessionRefreshMessageMissingData(): void {
		$msg = (object) [
			'type' => ProxyMessageType::PROXY_SESSION_REFRESH->value,
			'timestamp' => self::TIMESTAMP,
		];
		Assert::false(ProxyMessageValidator::isProxySessionRefreshMessage($msg));
	}

	/**
	 * Tests the function to check if message is proxy session refresh message (invalid data)
	 */
	public function testIsProxySessionRefreshMessageInvalidData(): void {
		$msg = (object) [
			'type' => ProxyMessageType::PROXY_SESSION_REFRESH->value,
			'timestamp' => self::TIMESTAMP,
			'data' => 'invalid',
		];
		Assert::false(ProxyMessageValidator::isProxySessionRefreshMessage($msg));
	}

	/**
	 * Tests the function to check if message is proxy session refresh message (missing session ID)
	 */
	public function testIsProxySessionRefreshMessageMissingSessionId(): void {
		$msg = (object) [
			'type' => ProxyMessageType::PROXY_SESSION_REFRESH->value,
			'timestamp' => self::TIMESTAMP,
			'data' => (object) [
				'token' => self::ACCESS_TOKEN,
			],
		];
		Assert::false(ProxyMessageValidator::isProxySessionRefreshMessage($msg));
	}

	/**
	 * Tests the function to check if message is proxy session refresh message (invalid session ID)
	 */
	public function testIsProxySessionRefreshMessageInvalidSessionId(): void {
		$msg = (object) [
			'type' => ProxyMessageType::PROXY_SESSION_REFRESH->value,
			'timestamp' => self::TIMESTAMP,
			'data' => (object) [
				'sessionId' => false,
				'token' => self::ACCESS_TOKEN,
			],
		];
		Assert::false(ProxyMessageValidator::isProxySessionRefreshMessage($msg));
	}

	/**
	 * Tests the function to check if message is proxy session refresh message (missing token)
	 */
	public function testIsProxySessionRefreshMessageMissingToken(): void {
		$msg = (object) [
			'type' => ProxyMessageType::PROXY_SESSION_REFRESH->value,
			'timestamp' => self::TIMESTAMP,
			'data' => (object) [
				'sessionId' => 1,
			],
		];
		Assert::false(ProxyMessageValidator::isProxySessionRefreshMessage($msg));
	}

	/**
	 * Tests the function to check if message is proxy session refresh message (invalid token)
	 */
	public function testIsProxySessionRefreshMessageInvalidToken(): void {
		$msg = (object) [
			'type' => ProxyMessageType::PROXY_SESSION_REFRESH->value,
			'timestamp' => self::TIMESTAMP,
			'data' => (object) [
				'sessionId' => 1,
				'token' => [],
			],
		];
		Assert::false(ProxyMessageValidator::isProxySessionRefreshMessage($msg));
	}

	/**
	 * Tests the function to check if message is auth success message
	 */
	public function testIsAuthSuccessMessageValid(): void {
		$msg = (object) [
			'type' => 'auth_success',
			'expiration' => self::TIMESTAMP,
			'service' => false,
		];
		Assert::true(ProxyMessageValidator::isAuthSuccessMessage($msg));
	}

	/**
	 * Tests the function to check if message is auth success message (missing type)
	 */
	public function testIsAuthSuccessMessageMissingType(): void {
		$msg = (object) [
			'expiration' => self::TIMESTAMP,
			'service' => false,
		];
		Assert::false(ProxyMessageValidator::isAuthSuccessMessage($msg));
	}

	/**
	 * Tests the function to check if message is auth success message (invalid type)
	 */
	public function testIsAuthSuccessMessageInvalidType(): void {
		$msg = (object) [
			'type' => 'auth',
			'expiration' => self::TIMESTAMP,
			'service' => false,
		];
		Assert::false(ProxyMessageValidator::isAuthSuccessMessage($msg));
	}

	/**
	 * Tests the function to check if message is auth success message (missing expiration)
	 */
	public function testIsAuthSuccessMessageMissingExpiration(): void {
		$msg = (object) [
			'type' => 'auth_success',
			'service' => false,
		];
		Assert::false(ProxyMessageValidator::isAuthSuccessMessage($msg));
	}

	/**
	 * Tests the function to check if message is auth success message (invalid expiration)
	 */
	public function testIsAuthSuccessMessageInvalidExpiration(): void {
		$msg = (object) [
			'type' => 'auth_success',
			'expiration' => true,
			'service' => false,
		];
		Assert::false(ProxyMessageValidator::isAuthSuccessMessage($msg));
	}

	/**
	 * Tests the function to check if message is auth success message (missing service mode permission)
	 */
	public function testIsAuthSuccessMessageMissingService(): void {
		$msg = (object) [
			'type' => 'auth_success',
			'expiration' => self::TIMESTAMP,
		];
		Assert::false(ProxyMessageValidator::isAuthSuccessMessage($msg));
	}

	/**
	 * Tests the function to check if message is auth success message (invalid service mode permission)
	 */
	public function testIsAuthSuccessMessageInvalidService(): void {
		$msg = (object) [
			'type' => 'auth_success',
			'expiration' => self::TIMESTAMP,
			'service' => 'yes',
		];
		Assert::false(ProxyMessageValidator::isAuthSuccessMessage($msg));
	}

	/**
	 * Tests the function to check if message is auth error message
	 */
	public function testIsAuthErrorMessageValid(): void {
		$msg = (object) [
			'type' => 'auth_failed',
			'code' => 4,
			'error' => 'invalid_token',
		];
		Assert::true(ProxyMessageValidator::isAuthErrorMessage($msg));
	}

	/**
	 * Tests the function to check if message is auth error message (missing type)
	 */
	public function testIsAuthErrorMessageMissingType(): void {
		$msg = (object) [
			'code' => 4,
			'error' => 'invalid_token',
		];
		Assert::false(ProxyMessageValidator::isAuthErrorMessage($msg));
	}

	/**
	 * Tests the function to check if message is auth error message (invalid type)
	 */
	public function testIsAuthErrorMessageInvalidType(): void {
		$msg = (object) [
			'type' => [],
			'code' => 4,
			'error' => 'invalid_token',
		];
		Assert::false(ProxyMessageValidator::isAuthErrorMessage($msg));
	}

	/**
	 * Tests the function to check if message is auth error message (missing code)
	 */
	public function testIsAuthErrorMessageMissingCode(): void {
		$msg = (object) [
			'type' => 'auth_failed',
			'error' => 'invalid_token',
		];
		Assert::false(ProxyMessageValidator::isAuthErrorMessage($msg));
	}

	/**
	 * Tests the function to check if message is auth error message (invalid code)
	 */
	public function testIsAuthErrorMessageInvalidCode(): void {
		$msg = (object) [
			'type' => 'auth_failed',
			'code' => 'invalid',
			'error' => 'invalid_token',
		];
		Assert::false(ProxyMessageValidator::isAuthErrorMessage($msg));
	}

	/**
	 * Tests the function to check if message is auth error message (missing error description)
	 */
	public function testIsAuthErrorMessageMissingError(): void {
		$msg = (object) [
			'type' => 'auth_failed',
			'code' => 4,
		];
		Assert::false(ProxyMessageValidator::isAuthErrorMessage($msg));
	}

	/**
	 * Tests the function to check if message is auth error message (invalid error description)
	 */
	public function testIsAuthErrorMessageInvalidError(): void {
		$msg = (object) [
			'type' => 'auth_failed',
			'code' => 4,
			'error' => true,
		];
		Assert::false(ProxyMessageValidator::isAuthErrorMessage($msg));
	}

	/**
	 * Tests the function to check if message is Daemon API message
	 */
	public function testIsDaemonApiMessageValid(): void {
		$msg = (object) [
			'mType' => 'message_type',
			'data' => (object) [
				'msgId' => 'message_id',
			],
		];
		Assert::true(ProxyMessageValidator::isDaemonApiMessage($msg));
	}

	/**
	 * Tests the function to check if message is Daemon API message (missing message type)
	 */
	public function testIsDaemonApiMessageMissingMtype(): void {
		$msg = (object) [
			'data' => (object) [
				'msgId' => 'message_id',
			],
		];
		Assert::false(ProxyMessageValidator::isDaemonApiMessage($msg));
	}

	/**
	 * Tests the function to check if message is Daemon API message (invalid message type)
	 */
	public function testIsDaemonApiMessageInvalidMtype(): void {
		$msg = (object) [
			'mType' => 1,
			'data' => (object) [
				'msgId' => 'message_id',
			],
		];
		Assert::false(ProxyMessageValidator::isDaemonApiMessage($msg));
	}

	/**
	 * Tests the function to check if message is Daemon API message (missing data)
	 */
	public function testIsDaemonApiMessageMissingData(): void {
		$msg = (object) [
			'mType' => 'message_type',
			'msgId' => 'message_id',
		];
		Assert::false(ProxyMessageValidator::isDaemonApiMessage($msg));
	}

	/**
	 * Tests the function to check if message is Daemon API message (invalid data)
	 */
	public function testIsDaemonApiMessageInvalidData(): void {
		$msg = (object) [
			'mType' => 'message_type',
			'data' => 'message_id',
		];
		Assert::false(ProxyMessageValidator::isDaemonApiMessage($msg));
	}

	/**
	 * Tests the function to check if message is Daemon API message (missing message id)
	 */
	public function testIsDaemonApiMessageMissingMsgId(): void {
		$msg = (object) [
			'mType' => 'message_type',
			'data' => (object) [],
		];
		Assert::false(ProxyMessageValidator::isDaemonApiMessage($msg));
	}

	/**
	 * Tests the function to check if message is Daemon API message (invalid message id)
	 */
	public function testIsDaemonApiMessageInvalidMsgId(): void {
		$msg = (object) [
			'mType' => 'message_type',
			'data' => (object) [
				'msgId' => 1,
			],
		];
		Assert::false(ProxyMessageValidator::isDaemonApiMessage($msg));
	}

}

$test = new ProxyMessageValidatorTest();
$test->run();
