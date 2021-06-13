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
import {AxiosError} from 'axios';
import {useI18n} from 'vue-i18n';
import {useToast} from 'vue-toastification';
import store from '../store';

/**
 * Form error handler
 */
class FormErrorHandler {

	/**
	 * Handles Config errors
	 * @param error caught axios error
	 */
	configError(error: AxiosError): void {
		const i18n = useI18n();
		const toast = useToast();
		store.commit('spinner/HIDE');
		if (error.response === undefined) {
			toast.error(i18n.t('config.daemon.messages.configFailed', {error: error.message}).toString());
			return;
		}
		if (error.response.status === 400) {
			toast.error(i18n.t('forms.messages.submitBadRequest').toString());
		} else if (error.response.status === 404) {
			toast.error(i18n.t('forms.messages.componentNotFound').toString());
		} else if (error.response.status === 500) {
			toast.error(i18n.t('forms.messages.submitServerError').toString());
		} else {
			console.error(error.message);
		}

	}

	/**
	 * Handles Service errors
	 * @param error caught axios error
	 */
	serviceError(error: AxiosError): void {
		const i18n = useI18n();
		const toast = useToast();
		store.commit('spinner/HIDE');
		if (error.response === undefined) {
			console.error(error);
			return;
		}
		if (error.response.status === 400) {
			toast.error(i18n.t('forms.messages.submitBadRequest').toString());
		} else if (error.response.status === 404) {
			toast.error(i18n.t('service.errors.unsupportedService').toString());
		} else if (error.response.status === 500) {
			toast.error(i18n.t('service.errors.unsupportedInit').toString());
		}
	}


	/**
	 * Handles mapping manager errors
	 * @param error Caught axios error
	 */
	mappingError(error: AxiosError): void {
		const i18n = useI18n();
		const toast = useToast();
		store.commit('spinner/HIDE');
		if (error.response === undefined) {
			console.error(error);
			return;
		}
		if (error.response.status === 400) {
			toast.error(i18n.t('config.interfaceMappings.messages.invalid').toString());
		} else if (error.response.status === 404) {
			toast.error(i18n.t('config.interfaceMappings.messages.notFound').toString());
		}
	}
}

export default new FormErrorHandler();
