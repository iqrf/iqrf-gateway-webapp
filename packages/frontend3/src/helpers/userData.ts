import { UserLanguage, UserRole, UserSessionExpiration } from '@iqrf/iqrf-gateway-webapp-client/types';
import { computed, ComputedRef } from 'vue';

import { SelectItem } from '@/types/vuetify';
import i18n from '@/plugins/i18n';

export function getLanguageOptions(): ComputedRef<Array<SelectItem>> {
	return computed(() => {
		const languages = Object.values(UserLanguage);
		return languages.map((item: UserLanguage): SelectItem => {
			return {
				title: i18n.global.t(`locale.languages.${item}`).toString(),
				value: item,
			};
		});
	});
}

export function getRoleOptions(): ComputedRef<Array<SelectItem>> {
	return computed(() => {
		const roles = Object.values(UserRole);
		return roles.map((item: UserRole): SelectItem => {
			return {
				title: i18n.global.t(`user.roles.${item}`).toString(),
				value: item,
			};
		});
	});
}

export function getExpirationOptions(): ComputedRef<Array<SelectItem>> {
	return computed(() => {
		const expirations = Object.values(UserSessionExpiration);
		return expirations.map((item: UserSessionExpiration): SelectItem => {
			return {
				title: i18n.global.t(`auth.sign.in.expirations.${item}`).toString(),
				value: item,
			};
		});
	});
}
