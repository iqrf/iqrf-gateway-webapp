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
import {ValidationRuleFunction} from 'vee-validate/dist/types/types';

// Autonetwork

/**
 * Validates HWPID filter string (comma separated HWPIDs)
 * @param {string} hwpids HWPID filter string to validate
 * @returns {boolean} True if HWPID filter is valid
 */
const hwpidFilter: ValidationRuleFunction = (hwpids: string): boolean => {
	const re = RegExp('^(6553[0-5]|655[0-2]\\d|65[0-4]\\d{2}|6[0-4]\\d{3}|[1-5]\\d{4}|[1-9]\\d{1,3}|\\d)(,(6553[0-5]|655[0-2]\\d|65[0-4]\\d{2}|6[0-4]\\d{3}|[1-5]\\d{4}|[1-9]\\d{1,3}|\\d))*$');
	return re.test(hwpids);
};

// IQMESH Network

/**
 * Validates Smart Connect code
 * @param code Code to validate
 * @returns {boolean} True if Smart Connect code is valid
 */
const smartConnectCode: ValidationRuleFunction = (code: string): boolean => {
	const re = RegExp('^[1-9a-km-tv-zA-HJ-NP-Z]{34}$');
	return re.test(code);
};

export {hwpidFilter, smartConnectCode};
