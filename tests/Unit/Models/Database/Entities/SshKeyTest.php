<?php

/**
 * TEST: App\Models\Database\Entities\SshKey
 * @covers App\Models\Database\Entities\SshKey
 * @phpVersion >= 7.4
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

namespace Tests\Unit\Models\Database\Entities;

use App\Models\Database\Entities\SshKey;
use Tester\Assert;
use Tester\Expect;
use Tester\TestCase;

require __DIR__ . '/../../../../bootstrap.php';

/**
 * Tests for SSH key database entity
 */
class SshKeyTest extends TestCase {

	/**
	 * @var string SSH key description
	 */
	private const DESCRIPTION = 'test-key';

	/**
	 * @var string SSH key type
	 */
	private const KEY_TYPE = 'ssh-rsa';

	/**
	 * @var string SSH key hash
	 */
	private const KEY_HASH = 'SHA256:JNjbaNWv2Nau8+R75Eq4f9j2JDdxS8fpFPp9cZVAcV0';

	/**
	 * @var string SSH public key
	 */
	private const PUBLIC_KEY = 'AAAAB3NzaC1yc2EAAAADAQABAAACAQCqql6MzstZYh1TmWWv11q5O3pISj2ZFl9HgH1JLknLLx44+tXfJ7mIrKNxOOwxIxvcBF8PXSYvobFYEZjGIVCEAjrUzLiIxbyCoxVyle7Q+bqgZ8SeeM8wzytsY+dVGcBxF6N4JS+zVk5eMcV385gG3Y6ON3EG112n6d+SMXY0OEBIcO6x+PnUSGHrSgpBgX7Ks1r7xqFa7heJLLt2wWwkARptX7udSq05paBhcpB0pHtA1Rfz3K2B+ZVIpSDfki9UVKzT8JUmwW6NNzSgxUfQHGwnW7kj4jp4AT0VZk3ADw497M2G/12N0PPB5CnhHf7ovgy6nL1ikrygTKRFmNZISvAcywB9GVqNAVE+ZHDSCuURNsAInVzgYo9xgJDW8wUw2o8U77+xiFxgI5QSZX3Iq7YLMgeksaO4rBJEa54k8m5wEiEE1nUhLuJ0X/vh2xPff6SQ1BL/zkOhvJCACK6Vb15mDOeCSq54Cr7kvS46itMosi/uS66+PujOO+xt/2FWYepz6ZlN70bRly57Q06J+ZJoc9FfBCbCyYH7U/ASsmY095ywPsBo1XQ9PqhnN1/YOorJ068foQDNVpm146mUpILVxmq41Cj55YKHEazXGsdBIbXWhcrRf4G2fJLRcGUr9q8/lERo9oxRm5JFX6TCmj6kmiFqv+Ow9gI0x8GvaQ==';

	/**
	 * @var SshKey SSH key entity
	 */
	private SshKey $entity;

	/**
	 * Sets up the test environment
	 */
	protected function setUp(): void {
		parent::setUp();
		$this->entity = new SshKey(self::KEY_TYPE, self::PUBLIC_KEY, self::KEY_HASH, self::DESCRIPTION);
		$this->entity->setCreatedAt();
	}

	/**
	 * Tests the function to return SSH key type
	 */
	public function testGetType(): void {
		Assert::same(self::KEY_TYPE, $this->entity->getType());
	}

	/**
	 * Tests the function to set SSH key type
	 */
	public function testSetType(): void {
		$expected = 'ssh-ed25519';
		$this->entity->setType($expected);
		Assert::same($expected, $this->entity->getType());
	}

	/**
	 * Tests the function to return SSH public key
	 */
	public function testGetKey(): void {
		Assert::same(self::PUBLIC_KEY, $this->entity->getKey());
	}

	/**
	 * Tests the function to set SSH public key
	 */
	public function testSetKey(): void {
		$expected = 'AAAAB3NzaC1yc2EAAAABIwAAAQEA879BJGYlPTLIuc9/R5MYiN4yc/YiCLcdBpSdzgK9Dt0Bkfe3rSz5cPm4wmehdE7GkVFXrBJ2YHqPLuM1yx1AUxIebpwlIl9f/aUHOts9eVnVh4NztPy0iSU/Sv0b2ODQQvcy2vYcujlorscl8JjAgfWsO3W4iGEe6QwBpVomcME8IU35v5VbylM9ORQa6wvZMVrPECBvwItTY8cPWH3MGZiK/74eHbSLKA4PY3gM4GHI450Nie16yggEg2aTQfWA1rry9JYWEoHS9pJ1dnLqZU3k/8OWgqJrilwSoC5rGjgp93iu0H8T6+mEHGRQe84Nk1y5lESSWIbn6P636Bl3uQ==';
		$this->entity->setKey($expected);
		Assert::same($expected, $this->entity->getKey());
	}

	/**
	 * Tests the function to get SSH key hash
	 */
	public function testGetHash(): void {
		Assert::same(self::KEY_HASH, $this->entity->getHash());
	}

	/**
	 * Tests the function to set SSH key hash
	 */
	public function testSetHash(): void {
		$expected = 'SHA256:aAzJHzgAHpY08hwRgwNJPa/hnlaHsne0aJfkfHfi+gc';
		$this->entity->setHash($expected);
		Assert::same($expected, $this->entity->getHash());
	}

	/**
	 * Tests the function to get SSH key description
	 */
	public function testGetDescription(): void {
		Assert::same(self::DESCRIPTION, $this->entity->getDescription());
	}

	/**
	 * Tests the function to set SSH key description
	 */
	public function testSetDescription(): void {
		$expected = 'new description';
		$this->entity->setDescription($expected);
		Assert::same($expected, $this->entity->getDescription());
	}

	/**
	 * Tests the function to set no SSH key description
	 */
	public function testSetDescriptionNull(): void {
		$this->entity->setDescription();
		Assert::null($this->entity->getDescription());
	}

	/**
	 * Tests the function to serialize SSH key entity to json object
	 */
	public function testJsonSerialize(): void {
		$expected = [
			'id' => null,
			'type' => 'ssh-rsa',
			'key' => 'ssh-rsa AAAAB3NzaC1yc2EAAAADAQABAAACAQCqql6MzstZYh1TmWWv11q5O3pISj2ZFl9HgH1JLknLLx44+tXfJ7mIrKNxOOwxIxvcBF8PXSYvobFYEZjGIVCEAjrUzLiIxbyCoxVyle7Q+bqgZ8SeeM8wzytsY+dVGcBxF6N4JS+zVk5eMcV385gG3Y6ON3EG112n6d+SMXY0OEBIcO6x+PnUSGHrSgpBgX7Ks1r7xqFa7heJLLt2wWwkARptX7udSq05paBhcpB0pHtA1Rfz3K2B+ZVIpSDfki9UVKzT8JUmwW6NNzSgxUfQHGwnW7kj4jp4AT0VZk3ADw497M2G/12N0PPB5CnhHf7ovgy6nL1ikrygTKRFmNZISvAcywB9GVqNAVE+ZHDSCuURNsAInVzgYo9xgJDW8wUw2o8U77+xiFxgI5QSZX3Iq7YLMgeksaO4rBJEa54k8m5wEiEE1nUhLuJ0X/vh2xPff6SQ1BL/zkOhvJCACK6Vb15mDOeCSq54Cr7kvS46itMosi/uS66+PujOO+xt/2FWYepz6ZlN70bRly57Q06J+ZJoc9FfBCbCyYH7U/ASsmY095ywPsBo1XQ9PqhnN1/YOorJ068foQDNVpm146mUpILVxmq41Cj55YKHEazXGsdBIbXWhcrRf4G2fJLRcGUr9q8/lERo9oxRm5JFX6TCmj6kmiFqv+Ow9gI0x8GvaQ== test-key',
			'hash' => 'SHA256:JNjbaNWv2Nau8+R75Eq4f9j2JDdxS8fpFPp9cZVAcV0',
			'description' => 'test-key',
			'createdAt' => Expect::type('string'),
		];
		Assert::equal($expected, $this->entity->jsonSerialize());
		$expected['description'] = null;
		$expected['key'] = 'ssh-rsa AAAAB3NzaC1yc2EAAAADAQABAAACAQCqql6MzstZYh1TmWWv11q5O3pISj2ZFl9HgH1JLknLLx44+tXfJ7mIrKNxOOwxIxvcBF8PXSYvobFYEZjGIVCEAjrUzLiIxbyCoxVyle7Q+bqgZ8SeeM8wzytsY+dVGcBxF6N4JS+zVk5eMcV385gG3Y6ON3EG112n6d+SMXY0OEBIcO6x+PnUSGHrSgpBgX7Ks1r7xqFa7heJLLt2wWwkARptX7udSq05paBhcpB0pHtA1Rfz3K2B+ZVIpSDfki9UVKzT8JUmwW6NNzSgxUfQHGwnW7kj4jp4AT0VZk3ADw497M2G/12N0PPB5CnhHf7ovgy6nL1ikrygTKRFmNZISvAcywB9GVqNAVE+ZHDSCuURNsAInVzgYo9xgJDW8wUw2o8U77+xiFxgI5QSZX3Iq7YLMgeksaO4rBJEa54k8m5wEiEE1nUhLuJ0X/vh2xPff6SQ1BL/zkOhvJCACK6Vb15mDOeCSq54Cr7kvS46itMosi/uS66+PujOO+xt/2FWYepz6ZlN70bRly57Q06J+ZJoc9FfBCbCyYH7U/ASsmY095ywPsBo1XQ9PqhnN1/YOorJ068foQDNVpm146mUpILVxmq41Cj55YKHEazXGsdBIbXWhcrRf4G2fJLRcGUr9q8/lERo9oxRm5JFX6TCmj6kmiFqv+Ow9gI0x8GvaQ==';
		$this->entity->setDescription();
		Assert::equal($expected, $this->entity->jsonSerialize());
	}

	/**
	 * Tests the function to convert SSH key entity to string
	 */
	public function testToString(): void {
		$expected = self::KEY_TYPE . ' ' . self::PUBLIC_KEY . ' ' . self::DESCRIPTION;
		Assert::same($expected, $this->entity->toString());
	}

	/**
	 * Tests the function to convert SSH key entity to string with null description
	 */
	public function testToStringNull(): void {
		$this->entity->setDescription();
		$expected = self::KEY_TYPE . ' ' . self::PUBLIC_KEY;
		Assert::same($expected, $this->entity->toString());
	}

}

$test = new SshKeyTest();
$test->run();
