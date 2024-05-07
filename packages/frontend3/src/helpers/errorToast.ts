import { type ErrorResponse } from '@iqrf/iqrf-gateway-webapp-client/types';
import { type AxiosError } from 'axios';
import { toast } from 'vue3-toastify';

import i18n from '@/plugins/i18n';

/// @deprecated
export function basicErrorToast(error: AxiosError, message: string, params: Record<string, string|number>|undefined = undefined): void {
	const translations = {
		error: error.response ? (error.response.data as ErrorResponse).message : error.message,
	};
	if (params !== undefined) {
		Object.assign(translations, params);
	}
	toast.error(
		i18n.global.t(message, translations).toString(),
	);
}
