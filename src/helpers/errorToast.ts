import i18n from '../i18n';
import store from '../store';
import Vue from 'vue';

import {AxiosError} from 'axios';
import {Dictionary} from 'vue-router/types/router';

/**
 * Shows error toast with assignable parameters
 * @param {AxiosError} error Axios error
 * @param {string} message Path to translation message
 * @param {Dict<string|number>|undefined} params Partial translations for message placeholders
 */
export function extendedErrorToast(error: AxiosError, message: string, params: Dictionary<string|number>|undefined = undefined): void {
	const translations = {
		error: error.response ? error.response.data.message : error.message
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
export function controllerErrorToast(error: AxiosError, message: string, params: Dictionary<string>|undefined = undefined): void {
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
export function daemonErrorToast(error: AxiosError, message: string, params: Dictionary<string>|undefined = undefined): void {
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
export function menderErrorToast(error: AxiosError, message: string, params: Dictionary<string>|undefined = undefined): void {
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
export function monitErrorToast(error: AxiosError, message: string, params: Dictionary<string>|undefined = undefined): void {
	if (params === undefined) {
		params = {service: 'Monit'};
	} else {
		Object.assign(params, {service: 'Monit'});
	}
	extendedErrorToast(error, message, params);
}

/**
 * Calls extended error toast with Pixla argument
 * @param {AxiosError} error Axios error
 * @param {string} message Path to translation message
 * @param {Dict<string>|undefined} params Partial translations for message placeholders
 */
export function pixlaErrorToast(error: AxiosError, message: string, params: Dictionary<string>|undefined = undefined): void {
	if (params === undefined) {
		params = {service: 'PIXLA'};
	} else {
		Object.assign(params, {service: 'PIXLA'});
	}
	extendedErrorToast(error, message, params);
}
