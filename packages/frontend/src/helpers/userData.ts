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

import { UserLanguage, UserRole } from '@iqrf/iqrf-gateway-webapp-client/types';
import { computed, type ComputedRef } from 'vue';

import i18n from '@/plugins/i18n';
import { type SelectItem } from '@/types/vuetify';

export function getLanguageOptions(): ComputedRef<SelectItem[]> {
	return computed(() => {
		const languages = Object.values(UserLanguage);
		return languages.map((item: UserLanguage): SelectItem => {
			return {
				title: i18n.global.t(`components.common.locale.languages.${item}`),
				value: item,
			};
		});
	});
}

export function getFilteredRoleOptions(role: UserRole): ComputedRef<SelectItem[]> {
	return computed(() => {
		const roles = [UserRole.Admin, UserRole.Normal, UserRole.Basic];
		if (role !== UserRole.Admin) {
			roles.splice(0, roles.indexOf(role));
		}
		return roles.map((item: UserRole): SelectItem => {
			return {
				title: i18n.global.t(`user.roles.${item}`),
				value: item,
			};
		});
	});
}
