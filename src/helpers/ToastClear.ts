/**
 * Copyright 2017-2022 IQRF Tech s.r.o.
 * Copyright 2019-2022 MICRORISC s.r.o.
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
import i18n from '@/i18n';
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
