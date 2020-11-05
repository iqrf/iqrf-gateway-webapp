import store from '../store';
import compareVersions from 'compare-versions';

/**
 * Checks if Daemon version is higher than version passed in argument
 * @param {string} version Version to compare Daemon version against
 * @returns {boolean} True if Daemon version is higher than passed version
 */
export function versionHigherThan(version: string): boolean {
	const daemonVersion = store.getters.daemonVersion;
	if (daemonVersion === '') {
		return false;
	}
	if (compareVersions.compare(daemonVersion, version, '>=')) {
		return true;
	}
	return false;
}
