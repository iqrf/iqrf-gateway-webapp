/**
 * Copyright 2017-2024 IQRF Tech s.r.o.
 * Copyright 2019-2024 MICRORISC s.r.o.
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

import { type ErrorResponse } from '@iqrf/iqrf-gateway-webapp-client/types';
import { type AxiosError } from 'axios';
import { toast } from 'vue3-toastify';

import i18n from '@/plugins/i18n';

/**
 * Error toast
 * @deprecated
 * @param {AxiosError} error Axios error
 * @param {string} message Message
 * @param {Record<string, string|number>|undefined} params Parameters
 */
export function basicErrorToast(error: AxiosError, message: string, params: Record<string, string|number>|undefined = undefined): void {
	const translations = {
		error: error.response ? (error.response.data as ErrorResponse).message : error.message,
	};
	if (params !== undefined) {
		Object.assign(translations, params);
	}
	toast.error(
		i18n.global.t(message, translations),
	);
}
