/**
 * Copyright 2017-2025 IQRF Tech s.r.o.
 * Copyright 2019-2025 MICRORISC s.r.o.
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

/**
 * Returns RF channel validation rules for specified RF band
 * @param {number} rfBand RF band
 * @return {Record<string, string|number>} RF channel input validation rules
 */
export function getRfChannelRules(rfBand: number): Record<string, string|number> {
	if (rfBand === 433) {
		return {rule: 'integer|between:0,16|required', min: 0, max: 16};
	} else if (rfBand == 868) {
		return {rule: 'integer|between:0,67|required', min: 0, max: 67};
	} else {
		return {rule: 'integer|between:0,255|required', min: 0, max: 255};
	}
}

/**
 * Returns RF channel validation error messages
 * @param {number} rfBand RF band
 * @return {Record<string, string>} RF channel validation error messages
 */
export function getRfChannelValidationMessages(rfBand: number): Record<string, string> {
	const message = i18n.t('iqrfnet.trConfiguration.form.messages.rfChannel.' + rfBand).toString();
	return {
		between: message,
		integer: message,
		required: message,
	};
}
 