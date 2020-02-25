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
use Nette\Database\Context;
use Nette\Security\Identity;
use Nette\Security\IIdentity;
use Psr\Http\Message\ServerRequestInterface;

class BasicAuthenticator implements IAuthenticator {

	/**
	 * @var Context Database context
	 */
	private $context;

	/**
	 * Constructor
	 * @param Context $context Database context
	 */
	public function __construct(Context $context) {
		$this->context = $context;
	}

	/**
	 * @inheritDoc
	 */
	public function authenticate(ServerRequestInterface $request): ?IIdentity {
		$table = $this->context->table('users');
		$credentials = $this->parseAuthorizationHeader($request->getHeader('Authorization')[0] ?? '');
		if ($credentials === null) {
			return null;
		}
		$row = $table->where('username', $credentials['username'])->fetch();
		if ($row === null) {
			return null;
		}
		if (!password_verify($credentials['password'], $row['password'])) {
			return null;
		}
		$data = ['username' => $row['username'], 'language' => $row['language']];
		return new Identity($row['id'], $row['role'], $data);
	}

	/**
	 * Parses the authorization header
	 * @param string $header Authorization header
	 * @return mixed[]|null Authentication credentials
	 */
	protected function parseAuthorizationHeader(string $header): ?array {
		if (strpos($header, 'Basic') !== 0) {
			return null;
		}
		$header = explode(':', (string) base64_decode(substr($header, 6), true), 2);
		return [
			'username' => $header[0],
			'password' => $header[1] ?? '',
		];
	}

}
