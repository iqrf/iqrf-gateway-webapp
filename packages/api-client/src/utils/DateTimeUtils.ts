/**
 * Copyright 2023-2024 MICRORISC s.r.o.
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

import {DateTime} from 'luxon';

/**
 * Date & time utilities
 * @internal
 */
export class DateTimeUtils {

	/**
	 * Serializes the date & time
	 * @param {string|null} dateTime Date & time to serialize
	 * @return {DateTime|null} Serialized date & time
	 */
	public static serialize(dateTime: DateTime|null): string|null {
		return dateTime === null ? null : dateTime.toISO();
	}

	/**
	 * Deserializes the date & time
	 * @param {DateTime|null} dateTime Date & time to deserialize
	 * @return {string|null} Deserialized date & time
	 */
	public static deserialize(dateTime: string|null): DateTime|null {
		return dateTime === null ? null : DateTime.fromISO(dateTime);
	}

}
