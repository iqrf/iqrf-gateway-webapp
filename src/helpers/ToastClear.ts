import i18n from '../i18n';
import Vue from 'vue';
import {ToastOptions} from 'vue-toast-notification';

/**
 * Toast class that automatically clears existing toasts
 */
class ToastClear {
	
	/**
	 * Error toast
	 * @param {string} message Toast message
	 * @param {number} duration Toast duration
	 */
	error(message: string, duration = 5000): void {
		Vue.$toast.clear();
		Vue.$toast.open(this.defaultOptions(message, duration, 'error'));
	}

	/**
	 * Info toast
	 * @param {string} message Toast message
	 * @param {number} duration Toast duration
	 */
	info(message: string, duration = 5000): void {
		Vue.$toast.clear();
		Vue.$toast.open(this.defaultOptions(message, duration, 'info'));
	}

	/**
	 * Success toast
	 * @param {string} message Toast message
	 * @param {number} duration Toast duration
	 */
	success(message: string, duration = 5000): void {
		Vue.$toast.clear();
		Vue.$toast.open(this.defaultOptions(message, duration, 'success'));
	}

	/**
	 * Warning toast
	 * @param {string} message Toast message
	 * @param {number} duration Toast duration
	 */
	warning(message: string, duration = 5000): void {
		Vue.$toast.clear();
		Vue.$toast.open(this.defaultOptions(message, duration, 'warning'));
	}

	/**
	 * Creates and returns default ToastOptions object
	 * @param {string} message: Toast message
	 * @param {number} duration: Toast duration
	 * @param {string} type: Toast type
	 * @returns {ToastOptions} ToastOptions object
	 */
	private defaultOptions(message: string, duration: number, type: string): ToastOptions {
		return {
			message: i18n.t(message).toString(),
			type: type,
			duration: duration,
			position: 'top',
			dismissible: true,
			pauseOnHover: true
		};
	}

}

export default new ToastClear();