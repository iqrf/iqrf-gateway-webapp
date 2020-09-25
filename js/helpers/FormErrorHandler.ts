import {AxiosError} from 'axios';
import i18n from '../i18n';
import store from '../store';
import Vue from 'vue';

/**
 * Form error handler
 */
class FormErrorHandler {
	/**
	 * Handles Cloud errors
	 * @param error caught axios error
	 */
	cloudError(error: AxiosError) {
		store.commit('spinner/HIDE');
		if (error.response) {
			if (error.response.status === 400) {
				Vue.$toast.error(i18n.t('forms.messages.submitBadRequest').toString());
			} else if (error.response.status) {
				Vue.$toast.error(i18n.t('forms.messages.submitServerError').toString());
			}
		} else {
			console.error(error.message);
		}
	}

	/**
	 * Handles Config errors
	 * @param error caught axios error
	 */
	configError(error: AxiosError) {
		store.commit('spinner/HIDE');
		if (error.response) {
			if (error.response.status === 404) {
				Vue.$toast.error(i18n.t('forms.messages.componentNotFound').toString());
			} else if (error.response.status === 500) {
				Vue.$toast.error(i18n.t('forms.messages.submitServerError').toString());
			} else {
				console.error(error.message);
			}
		} else {
			console.error(error.message);
		}
	}

	/**
	 * Handles Service errors
	 * @param error caught axios error
	 */
	serviceError(error: AxiosError) {
		store.commit('spinner/HIDE');
		if (error.response) {
			if (error.response.status === 400) {
				Vue.$toast.error(i18n.t('forms.messages.submitBadRequest').toString());
			} else if (error.response.status === 404) {
				Vue.$toast.error(i18n.t('service.errors.unsupportedService').toString());
			} else if (error.response.status === 500) {
				Vue.$toast.error(i18n.t('service.errors.unsupportedInit').toString());
			}
		} else {
			console.error(error.message);
		}
	}
}

export default new FormErrorHandler();
