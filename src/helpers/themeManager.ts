/**
 * Copyright 2017-2023 IQRF Tech s.r.o.
 * Copyright 2019-2023 MICRORISC s.r.o.
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

import GenericBlueLogo from '@/assets/themes/generic/logo-blue.svg';
import GenericSmallLogo from '@/assets/themes/generic/logo-small.svg';
import GenericWhiteLogo from '@/assets/themes/generic/logo-white.svg';

import IqarosRedLogo from '@/assets/themes/iqaros/logo-red.svg';
import IqarosSmallLogo from '@/assets/themes/iqaros/logo-small.svg';
import IqarosWhiteLogo from '@/assets/themes/iqaros/logo-white.svg';

/**
 * URL builder
 */
export default class ThemeManager {

	/**
	 * Returns the theme name
	 * @return {string} Theme name
	 */
	static getName(): string {
		const name = process.env.VUE_APP_THEME ?? 'generic';
		const themes = ['generic', 'iqaros'];
		return themes.includes(name) ? name : 'generic';
	}

	/**
	 * Returns the title translation key
	 * @return {string} Title translation key
	 */
	static getTitleKey(): string {
		return 'core.title.' + ThemeManager.getName();
	}

	/**
	 * Returns the primary color
	 * @return {string} Primary color
	 */
	static getPrimaryColor(): string {
		switch (ThemeManager.getName()) {
			case 'iqaros':
				return '#e31e24';
			default:
				return '#337ab7';
		}
	}

	/**
	 * Returns small logo for the sidebar
	 * @return {any} Small logo for the sidebar
	 */
	static getSidebarSmallLogo(): any {
		switch (ThemeManager.getName()) {
			case 'iqaros':
				return IqarosSmallLogo;
			default:
				return GenericSmallLogo;
		}
	}

	/**
	 * Returns logo for the sidebar
	 * @return {any} Logo for the sidebar
	 */
	static getSidebarLogo(): any {
		switch (ThemeManager.getName()) {
			case 'iqaros':
				return IqarosWhiteLogo;
			default:
				return GenericWhiteLogo;
		}
	}

	/**
	 * Returns logo for the wizard
	 * @return {any} Logo for the wizard
	 */
	static getWizardLogo(): any {
		switch (ThemeManager.getName()) {
			case 'iqaros':
				return IqarosRedLogo;
			default:
				return GenericBlueLogo;
		}
	}

}
