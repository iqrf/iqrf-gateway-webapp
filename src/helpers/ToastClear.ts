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

import {useToast} from 'vue-toastification';

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
		const toast = useToast();
		toast.clear();
		toast.error(message, {timeout: duration});
	}

	/**
	 * Info toast
	 * @param {string} message Toast message
	 * @param {number} duration Toast duration
	 */
	info(message: string, duration = 5000): void {
		const toast = useToast();
		toast.clear();
		toast.info(message, {timeout: duration});
	}

	/**
	 * Success toast
	 * @param {string} message Toast message
	 * @param {number} duration Toast duration
	 */
	success(message: string, duration = 5000): void {
		const toast = useToast();
		toast.clear();
		toast.success(message, {timeout: duration});
	}

	/**
	 * Warning toast
	 * @param {string} message Toast message
	 * @param {number} duration Toast duration
	 */
	warning(message: string, duration = 5000): void {
		const toast = useToast();
		toast.clear();
		toast.warning(message, {timeout: duration});
	}

}

export default new ToastClear();
