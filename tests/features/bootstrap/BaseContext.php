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

namespace Tests\features\bootstrap;

use Behat\Behat\Context\Context;
use Behat\Gherkin\Node\TableNode;
use Exception;
use GuzzleHttp\Client;
use Nette\Utils\Json;
use Psr\Http\Message\ResponseInterface;

/**
 * Defines application features from the specific context.
 */
final class BaseContext implements Context {

	/**
	 * @var Client HTTP(S) client
	 */
	protected $client;

	/**
	 * @var ResponseInterface HTTP(S) response
	 */
	protected $response;

	/**
	 * @var string|null JWT
	 */
	protected $jwt;

	/**
	 * API path
	 */
	protected const API_PATH = '/api/v0/';

	/**
	 * HTTP body
	 */
	private const HTTP_BODY = 'body';

	/**
	 * HTTP headers
	 */
	private const HTTP_HEADERS = 'headers';

	/**
	 * Initializes context.
	 *
	 * Every scenario gets its own context instance.
	 * You can also pass arbitrary arguments to the
	 * context constructor through behat.yml.
	 */
	public function __construct() {
		$this->client = new Client([
			'base_uri' => 'http://localhost:8080/',
			'http_errors' => false,
		]);
	}

	/**
	 * @Given I log in as :name with password :password
	 * @param string $name Username
	 * @param string $password Password
	 */
	public function iLogInAsWithPassword(string $name, string $password): void {
		$jsonBody = ['username' => $name, 'password' => $password];
		$response = $this->client->post(self::API_PATH . '/user/signIn', ['json' => $jsonBody]);
		if ($response->getStatusCode() !== 200) {
			throw new Exception('Invalid credentials');
		}
		$this->jwt = Json::decode($response->getBody()->getContents())->token;
	}

	/**
	 * @Given I am an authenticated user
	 */
	public function iAmAnAuthenticatedUser(): void {
		$response = $this->client->get(self::API_PATH . '/user', $this->getClientOptions());
		if ($response->getStatusCode() !== 200) {
			throw new Exception('Unexpected HTTP status code: ' . $response->getStatusCode());
		}
	}

	/**
	 * @Given I am an unauthenticated user
	 */
	public function iAmAnUnauthenticatedUser(): void {
		$response = $this->client->get(self::API_PATH . '/user', $this->getClientOptions());
		if ($response->getStatusCode() !== 401) {
			throw new Exception('Unexpected HTTP status code: ' . $response->getStatusCode());
		}
	}

	/**
	 * @When I create HTTP :method request to :url without body
	 * @param string $method HTTP method
	 * @param string $url Requested URL
	 */
	public function iCreateHttpRequestToWithoutBody(string $method, string $url): void {
		$this->response = $this->client->request($method, self::API_PATH . $url, $this->getClientOptions());
	}

	/**
	 * @When I create HTTP :method request to :url with body :body
	 * @param string $method HTTP method
	 * @param string $url Requested URL
	 * @param string $body HTTP request body
	 */
	public function iCreateHttpRequestToWithBody(string $method, string $url, string $body): void {
		$options = $this->getClientOptions();
		$options[self::HTTP_BODY] = $body;
		$this->response = $this->client->request($method, self::API_PATH . $url, $options);
	}

	/**
	 * @When I create HTTP :method request to :url with JSON object body:
	 * @param string $method HTTP method
	 * @param string $url Requested URL
	 * @param TableNode $table JSON object body in table
	 */
	public function iCreateHttpRequestToWithJsonObjectBody(string $method, string $url, TableNode $table): void {
		$options = $this->getClientOptions();
		$options[self::HTTP_BODY] = Json::encode($table->getHash()[0]);
		$this->response = $this->client->request($method, self::API_PATH . $url, $options);
	}


	/**
	 * @Then HTTP status code is :statusCode
	 * @param int $statusCode HTTP status code
	 */
	public function httpStatusCodeIs(int $statusCode): void {
		$code = $this->response->getStatusCode();
		if ($code !== $statusCode) {
			throw new Exception('Unexpected HTTP status code: ' . $code);
		}
	}

	/**
	 * @Then HTTP response contains :response
	 * @param string $response HTTP response
	 */
	public function httpResponseContains(string $response): void {
		$actual = $this->response->getBody()->getContents();
		if ($actual !== $response) {
			throw new Exception('Unexpected HTTP response body: ' . $actual);
		}
	}

	/**
	 * @Then HTTP response contains JSON object:
	 * @param TableNode $table JSON object in table
	 */
	public function httpResponseContainsJsonObject(TableNode $table): void {
		$body = $this->response->getBody()->getContents();
		$actual = Json::decode($body, Json::FORCE_ARRAY);
		$expected = $table->getHash()[0];
		foreach ($expected as $key => $value) {
			$this->correctType($actual[$key], $value);
			if ((!array_key_exists($key, $actual)) ||
				$actual[$key] !== $value) {
				throw new Exception('Unexpected HTTP response body: ' . $body);
			}
		}
	}

	/**
	 * @Then HTTP response contains JSON array of objects:
	 * @param TableNode $table JSON array of objects in table
	 */
	public function httpResponseContainsJsonArrayOfObjects(TableNode $table): void {
		$body = $this->response->getBody()->getContents();
		$actual = Json::decode($body, Json::FORCE_ARRAY);
		$expected = $table->getHash();
		foreach ($expected as $index => $object) {
			foreach ($object as $key => $value) {
				$this->correctType($actual[$index][$key], $value);
				if ((!array_key_exists($key, $actual[$index])) ||
					$actual[$index][$key] !== $value) {
					throw new Exception('Unexpected HTTP response body: ' . $body);
				}
			}
		}
	}

	/**
	 * @When I edit optional feature :name:
	 * @param string $name Feature name
	 * @param TableNode $table Feature configuration
	 */
	public function iEditOptionalFeature(string $name, TableNode $table): void {
		$options = $this->getClientOptions();
		$row = $table->getHash()[0];
		$row['enabled'] = $row['enabled'] === 'true';
		$options[self::HTTP_BODY] = Json::encode($row);
		$this->response = $this->client->request('PUT', self::API_PATH . '/features/' . $name, $options);
	}


	/**
	 * @Then Optional feature :feature has configuration:
	 * @param string $feature Optional feature name
	 * @param TableNode $table Optional feature configuration in table
	 */
	public function optionalFeatureHasConfiguration(string $feature, TableNode $table): void {
		$response = $this->client->get(self::API_PATH . '/features/' . $feature, $this->getClientOptions());
		if ($response->getStatusCode() !== 200) {
			throw new Exception('Unexpected HTTP status code: ' . $response->getStatusCode());
		}
		$body = $response->getBody()->getContents();
		$actual = Json::decode($body, Json::FORCE_ARRAY);
		$expected = $table->getHash()[0];
		foreach ($expected as $key => $value) {
			$this->correctType($actual[$key], $value);
			if ((!array_key_exists($key, $actual)) ||
				$actual[$key] !== $value) {
				throw new Exception('Unexpected key "' . $key . '" value: ' . $value);
			}
		}
	}

	/**
	 * Corrects types
	 * @param mixed $actual Actual value
	 * @param mixed $expected Expected value
	 */
	private function correctType($actual, &$expected): void {
		if (is_bool($actual)) {
			$expected = mb_strtolower($expected, 'UTF-8') === 'true';
		} elseif (is_float($actual)) {
			$expected = (float) $expected;
		} elseif (is_int($actual)) {
			$expected = (int) $expected;
		}
	}

	/**
	 * Returns the HTTP client options
	 * @return array<string, array<string, string>> HTTP client options
	 */
	private function getClientOptions(): array {
		$options = [];
		if (isset($this->jwt)) {
			$options[self::HTTP_HEADERS] = ['Authorization' => 'Bearer ' . $this->jwt];
		}
		return $options;
	}

}
