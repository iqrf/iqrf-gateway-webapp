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

import { UserThemePreference } from '@iqrf/iqrf-gateway-webapp-client/types';
import { Theme } from '@iqrf/iqrf-ui-common-types';
import { defineStore } from 'pinia';

import { PreferenceDefaults } from '@/helpers/PreferenceDefaults';

/**
 * Theme store state
 */
interface ThemeState {
	/// Current theme
	current: Theme;
	/// Has been the theme toggled?
	toggled: boolean;
}

export const useThemeStore = defineStore('theme', {
	state: (): ThemeState => ({
		current: PreferenceDefaults.getSystemTheme(),
		toggled: false,
	}),
	actions: {
		/**
		 * Sets the theme from preferences
		 * @param {UserThemePreference} preference Theme preference
		 */
		setTheme(preference: UserThemePreference): void {
			if (preference === UserThemePreference.Auto) {
				if (this.toggled) {
					return;
				}
				this.current = PreferenceDefaults.getSystemTheme();
				return;
			}
			this.current = preference === UserThemePreference.Dark ? Theme.Dark : Theme.Light;
			this.toggled = false;
		},
		/**
		 * Toggles the current theme
		 */
		toggleTheme(): void {
			this.current = this.current === Theme.Dark ? Theme.Light : Theme.Dark;
			this.toggled = true;
		},
	},
	getters: {
		/**
		 * Returns the current theme
		 * @return {Theme} Current theme
		 */
		getTheme(): Theme {
			return this.current;
		},
	},
	persist: true,
});
