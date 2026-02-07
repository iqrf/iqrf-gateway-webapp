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
import { Theme } from '@iqrf/iqrf-ui-common-types';

import { TimeFormat } from '@/types/time';

/**
 * Preference defaults utility
 */
export class PreferenceDefaults {

	/**
	 * Returns the default theme based on the system settings
	 * @return {Theme} Default theme
	 */
	public static getSystemTheme(): Theme {
		if (window.matchMedia('(prefers-color-scheme: dark)').matches) {
			return Theme.Dark;
		}
		return Theme.Light;
	}

	/**
	 * Returns the default time format based on the system settings
	 * @return {TimeFormat} Default time format
	 */
	public static getSystemTimeFormat(): TimeFormat {
		const browserFormat = new Intl.DateTimeFormat(undefined, { hour: 'numeric' }).resolvedOptions().hourCycle;
		if (browserFormat === undefined) {
			return TimeFormat.Hour24;
		}
		return browserFormat.startsWith('h1') ? TimeFormat.Hour12 : TimeFormat.Hour24;
	}

}
