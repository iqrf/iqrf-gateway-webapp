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

namespace App\ApiModule\Version0\Models;

use Contributte\Middlewares\Security\IAuthenticator;
use DateTimeImmutable;
use Lcobucci\JWT\Token\Plain;
use Nette\Database\Context;
use Nette\Security\Identity;
use Nette\Security\IIdentity;
use Psr\Http\Message\ServerRequestInterface;
use Throwable;

class JwtAuthenticator implements IAuthenticator {

	/**
	 * @var Context Database context
	 */
	private $database;

	/**
	 * @var JwtConfigurator JWT configurator
	 */
	private $configurator;

	/**
	 * Constructor
	 * @param JwtConfigurator $configurator JWT configurator
	 * @param Context $database Database context
	 */
	public function __construct(JwtConfigurator $configurator, Context $database) {
		$this->configurator = $configurator;
		$this->database = $database;
	}

	/**
	 * @inheritDoc
	 */
	public function authenticate(ServerRequestInterface $request): ?IIdentity {
		$header = $request->getHeader('Authorization')[0] ?? '';
		$jwt = $this->parseAuthorizationHeader($header);
		if ($jwt === null) {
			return null;
		}
		/** @var Plain $token */
		$token = $this->configurator->create()->getParser()->parse($jwt);
		$now = new DateTimeImmutable();
		$hostname = gethostname();
		if ($token->isExpired($now) ||
			!$token->claims()->has('uid') ||
			!$token->hasBeenIssuedBefore($now) ||
			!$token->hasBeenIssuedBy($hostname) ||
			!$token->isIdentifiedBy($hostname)) {
			return null;
		}
		try {
			$id = $token->claims()->get('uid');
			$table = $this->database->table('users');
			$row = $table->where('id', $id)->fetch();
			if ($row === null) {
				return null;
			}
			$data = ['username' => $row['username'], 'language' => $row['language']];
			return new Identity($row['id'], $row['role'], $data);
		} catch (Throwable $e) {
			return null;
		}
	}

	/**
	 * Parses the authorization header
	 * @param string $header Authorization header
	 * @return string|null JWT
	 */
	protected function parseAuthorizationHeader(string $header): ?string {
		if (strpos($header, 'Bearer') !== 0) {
			return null;
		}
		$str = substr($header, 7);
		if ($str === false) {
			$str = null;
		}
		return $str;
	}

}
