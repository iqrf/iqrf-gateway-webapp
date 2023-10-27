import { UserLanguage, UserRole, UserSessionExpiration } from '@iqrf/iqrf-gateway-webapp-client/types';
import { computed, type ComputedRef } from 'vue';

import i18n from '@/plugins/i18n';
import { type SelectItem } from '@/types/vuetify';

export function getLanguageOptions(): ComputedRef<SelectItem[]> {
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

export function getRoleOptions(): ComputedRef<SelectItem[]> {
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

export function getFilteredRoleOptions(role: UserRole): ComputedRef<SelectItem[]> {
	return computed(() => {
		const roles = [UserRole.Admin, UserRole.Normal, UserRole.BasicAdmin, UserRole.Basic];
		if (role !== UserRole.Admin) {
			roles.splice(0, roles.indexOf(role));
		}
		return roles.map((item: UserRole): SelectItem => {
			return {
				title: i18n.global.t(`user.roles.${item}`).toString(),
				value: item,
			};
		});
	});
}

export function getExpirationOptions(): ComputedRef<SelectItem[]> {
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
