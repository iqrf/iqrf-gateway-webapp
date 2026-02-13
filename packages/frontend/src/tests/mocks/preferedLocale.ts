/**
 * Copyright 2017-2026 IQRF Tech s.r.o.
 * Copyright 2019-2026 MICRORISC s.r.o.
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

import { vi } from 'vitest';

/**
 * Mocks the preferred locale by spying on the Intl.DateTimeFormat constructor
 * and returning a new instance with the specified locale
 * @param {string} locale The locale to mock
 */
export function mockPreferredLocale(locale: string): void {
	const OriginalDateTimeFormat = Intl.DateTimeFormat;
	vi.spyOn(globalThis.Intl, 'DateTimeFormat')
		.mockImplementation(function (
			this: Intl.DateTimeFormat,
			_locales?: Intl.LocalesArgument,
			options?: Intl.DateTimeFormatOptions,
		): Intl.DateTimeFormat {
			return new OriginalDateTimeFormat(locale, options);
		});
}
