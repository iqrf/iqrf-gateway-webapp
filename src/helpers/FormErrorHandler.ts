import {AxiosError} from 'axios';
import i18n from '../i18n';
import store from '../store';
import Vue from 'vue';

/**
 * Form error handler
 */
class FormErrorHandler {

	/**
	 * Handles Config errors
	 * @param error caught axios error
	 */
	configError(error: AxiosError): void {
		store.commit('spinner/HIDE');
		if (error.response === undefined) {
			Vue.$toast.error(i18n.t('config.daemon.messages.configFailed', {error: error.message}).toString());
			return;
		}
		if (error.response.status === 400) {
			Vue.$toast.error(i18n.t('forms.messages.submitBadRequest').toString());
		} else if (error.response.status === 404) {
			Vue.$toast.error(i18n.t('forms.messages.componentNotFound').toString());
		} else if (error.response.status === 500) {
			Vue.$toast.error(i18n.t('forms.messages.submitServerError').toString());
		} else {
			console.error(error.message);
		}

	}

	/**
	 * Handles Service errors
	 * @param error caught axios error
	 */
	serviceError(error: AxiosError): void {
		store.commit('spinner/HIDE');
		if (error.response === undefined) {
			console.error(error);
			return;
		}
		if (error.response.status === 400) {
			Vue.$toast.error(i18n.t('forms.messages.submitBadRequest').toString());
		} else if (error.response.status === 404) {
			Vue.$toast.error(i18n.t('service.errors.unsupportedService').toString());
		} else if (error.response.status === 500) {
			Vue.$toast.error(i18n.t('service.errors.unsupportedInit').toString());
		}
	}


	/**
	 * Handles mapping manager errors
	 * @param error Caught axios error
	 */
	mappingError(error: AxiosError): void {
		store.commit('spinner/HIDE');
		if (error.response === undefined) {
			console.error(error);
			return;
		}
		if (error.response.status === 400) {
			Vue.$toast.error(i18n.t('config.interfaceMappings.messages.invalid').toString());
		} else if (error.response.status === 404) {
			Vue.$toast.error(i18n.t('config.interfaceMappings.messages.notFound').toString());
		}
	}
}

export default new FormErrorHandler();
