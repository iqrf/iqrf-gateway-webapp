/**
 * Copyright 2017-2021 IQRF Tech s.r.o.
 * Copyright 2019-2021 MICRORISC s.r.o.
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
import i18n from '@/plugins/i18n';
import store from '@/store';
import Vue from 'vue';

import {AxiosError} from 'axios';
import {ErrorResponse} from '@/types';

/**
 * Shows error toast with assignable parameters
 * @param {AxiosError} error Axios error
 * @param {string} message Path to translation message
 * @param {Dict<string|number>|undefined} params Partial translations for message placeholders
 */
export function extendedErrorToast(error: AxiosError, message: string, params: Record<string, string|number>|undefined = undefined): void {
	const translations = {
		error: error.response ? (error.response.data as ErrorResponse).message : error.message
	};
	if (params !== undefined) {
		Object.assign(translations, params);
	}
	store.commit('spinner/HIDE');
	Vue.$toast.error(i18n.t(message, translations).toString());
}

/**
 * Calls extended error toast with IQRF Gateway Controller argument
 * @param {AxiosError} error Axios error
 * @param {string} message Path to translation message
 * @param {Dict<string>|undefined} params Partial translations for message placeholders
 */
export function controllerErrorToast(error: AxiosError, message: string, params: Record<string, string>|undefined = undefined): void {
	if (params === undefined) {
		params = {service: 'IQRF Gateway Controller'};
	} else {
		Object.assign(params, {service: 'IQRF Gateway Controller'});
	}
	extendedErrorToast(error, message, params);
}

/**
 * Calls extended error toast with IQRF Gateway Daemon argument
 * @param {AxiosError} error Axios error
 * @param {string} message Path to translation message
 * @param {Dict<string>|undefined} params Partial translations for message placeholders
 */
export function daemonErrorToast(error: AxiosError, message: string, params: Record<string, string>|undefined = undefined): void {
	if (params === undefined) {
		params = {service: 'IQRF Gateway Daemon'};
	} else {
		Object.assign(params, {service: 'IQRF Gateway Daemon'});
	}
	extendedErrorToast(error, message, params);
}

/**
 * Calls extended error toast with Mender argument
 * @param {AxiosError} error Axios error
 * @param {string} message Path to translation message
 * @param {Dict<string>|undefined} params Partial translations for message placeholders
 */
export function menderErrorToast(error: AxiosError, message: string, params: Record<string, string>|undefined = undefined): void {
	if (params === undefined) {
		params = {service: 'Mender'};
	} else {
		Object.assign(params, {service: 'Mender'});
	}
	extendedErrorToast(error, message, params);
}

/**
 * Calls extended error toast with Monit argument
 * @param {AxiosError} error Axios error
 * @param {string} message Path to translation message
 * @param {Dict<string>|undefined} params Partial translations for message placeholders
 */
export function monitErrorToast(error: AxiosError, message: string, params: Record<string, string>|undefined = undefined): void {
	if (params === undefined) {
		params = {service: 'Monit'};
	} else {
		Object.assign(params, {service: 'Monit'});
	}
	extendedErrorToast(error, message, params);
}
