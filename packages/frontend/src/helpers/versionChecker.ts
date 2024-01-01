/**
 * Copyright 2017-2024 IQRF Tech s.r.o.
 * Copyright 2019-2024 MICRORISC s.r.o.
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
import store from '@/store';
import {compare, CompareOperator} from 'compare-versions';

/**
 * Compares the Daemon version against version and operation passed in arguments
 * @param version Version to compare Daemon version against
 * @param {CompareOperator} operator Comparison operator
 * @return {boolean} Comparison result
 */
export function versionCompare(version: string, operator: CompareOperator): boolean {
	const daemonVersion = store.getters['daemonClient/getVersion'];
	if (daemonVersion === '') {
		return false;
	}
	return compare(daemonVersion, version, operator);

}

/**
 * Checks if Daemon version is higher than version passed in argument
 * @param {string} version Version to compare Daemon version against
 * @returns {boolean} True if Daemon version is higher than passed version
 */
export function versionHigherEqual(version: string): boolean {
	return versionCompare(version, '>=');
}

/**
 * Checks if Daemon version is lower than version passed in argument
 * @param {string} version Version to compare Daemon version against
 * @returns {boolean} True if Daemon version is lower than passed version
 */
export function versionLowerEqual(version: string): boolean {
	return versionCompare(version, '<=');
}
