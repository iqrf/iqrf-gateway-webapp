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
		if (error.response === undefined) {
			console.error(error);
			return;
		}
		if (error.response.status === 400) {
			Vue.$toast.error(i18n.t('forms.messages.submitBadRequest').toString());
		} else if (error.response.status) {
			Vue.$toast.error(i18n.t('forms.messages.submitServerError').toString());
		}
	}

	/**
	 * Handles Config errors
	 * @param error caught axios error
	 */
	configError(error: AxiosError) {
		store.commit('spinner/HIDE');
		if (error.response === undefined) {
			console.error(error);
			return;
		}
		if (error.response.status === 404) {
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
	serviceError(error: AxiosError) {
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
	 * Handles Scheduler errors
	 * @param error caugh axios error
	 */
	schedulerError(error: AxiosError) {
		store.commit('spinner/HIDE');
		if (error.response === undefined) {
			console.error(error);
			return;
		}
		if (error.response.status === 400) {
			Vue.$toast.error(i18n.t('config.scheduler.messages.rest.invalidTask').toString());
		} else if (error.response.status === 404) {
			Vue.$toast.error(i18n.t('config.scheduler.messages.rest.notFound').toString());
		} else if (error.response.status === 409) {
			Vue.$toast.error(i18n.t('config.scheduler.messages.rest.exists').toString());
		}
	}

	/**
	 * Handles API key management errors
	 * @param error caught axios error
	 */
	apiKeyError(error: AxiosError) {
		store.commit('spinner/HIDE');
		if (error.response === undefined) {
			console.error(error);
			return;
		}
		if (error.response.status === 400) {
			Vue.$toast.error(i18n.t('config.apiKey.messages.invalid').toString());
		} else if (error.response.status === 404) {
			Vue.$toast.error(i18n.t('config.apiKey.messages.notFound').toString());
		}
	}
}

export default new FormErrorHandler();
