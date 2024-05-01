import { type ThemeDefinition } from 'vuetify';

import { AppTheme } from '@/types/vuetify';

export default class ThemeManager {
	
	public static getDarkTheme(): ThemeDefinition {
		switch (import.meta.env.VITE_THEME) {
			case AppTheme.IQAROS:
				return {
					colors: {
						primary: '#e31e24',
						background: '#ebedef',
					},
				};
			default:
				return {
					colors: {
						primary: '#367fa9',
						background: '#ebedef',
					},
				};
		}
	}

	public static getLightTheme(): ThemeDefinition {
		switch (import.meta.env.VITE_THEME) {
			case AppTheme.IQAROS:
				return {
					colors: {
						primary: '#e31e24',
						background: '#ebedef',
					},
				};
			default:
				return {
					colors: {
						primary: '#367fa9',
						background: '#ebedef',
					},
				};
		}
	}
	
}
