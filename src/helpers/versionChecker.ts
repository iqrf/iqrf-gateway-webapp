import store from '../store';
import compareVersions, {CompareOperator} from 'compare-versions';

/**
 * Compares the Daemon version against version and operation passed in arguments
 * @param version Version to compare Daemon version against
 * @param {CompareOperator} operator Comparison operator
 * @return {boolean} Comparison result
 */
export function versionCompare(version: string, operator: CompareOperator): boolean {
	const daemonVersion = store.getters.daemonVersion;
	if (daemonVersion === '') {
		return false;
	}
	return compareVersions.compare(daemonVersion, version, operator);

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
