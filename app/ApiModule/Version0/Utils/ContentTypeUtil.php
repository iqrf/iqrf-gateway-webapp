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

namespace App\ApiModule\Version0\Utils;

use Apitte\Core\Exception\Api\ClientErrorException;
use Apitte\Core\Http\ApiRequest;
use Apitte\Core\Http\ApiResponse;

/**
 * Request content type utility
 */
class ContentTypeUtil {

	/**
	 * Returns content type header if it exists
	 * @param ApiRequest $request API request
	 * @return array<string> content-type header
	 */
	public static function getContentType(ApiRequest $request): ?array {
		$headers = $request->getHeaders();
		if (array_key_exists('content-type', $headers)) {
			return $headers['content-type'];
		}
		return null;
	}

	/**
	 * Returns content type string if it is valid
	 * @param ApiRequest $request API request
	 * @param array<string> $contentTypes array of allowed content types
	 */
	public static function validContentType(ApiRequest $request, array $contentTypes): ?string {
		$contentType = self::getContentType($request);
		if ($contentType !== null) {
			foreach ($contentTypes as $item) {
				if (strpos($contentType[0], $item) !== false) {
					return $item;
				}
			}
			throw new ClientErrorException('Invalid content type', ApiResponse::S415_UNSUPPORTED_MEDIA_TYPE);
		}
		throw new ClientErrorException('Missing content-type header', ApiResponse::S422_UNPROCESSABLE_ENTITY);
	}

}
