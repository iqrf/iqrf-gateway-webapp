import axios, {AxiosResponse} from 'axios';
import store from '../store';

/**
 * Installation check interface
 */
export interface InstallationCheck {
	allMigrationsExecuted: boolean,
	hasUsers?: boolean,
	phpModules: InstallationCheckPhp,
	sudo?: InstallationCheckSudo,
}

/**
 * Installation check php extensions interface
 */
export interface InstallationCheckPhp {
	allExtensionsLoaded: boolean,
	missing?: InstallationCheckPhpMissing,
}

export interface InstallationCheckPhpMissing {
	extensions: Array<string>,
	packages?: Array<string>,
}

/**
 * Installation check sudo interface
 */
export interface InstallationCheckSudo {
	user: string
	exists: boolean,
	userSudo: boolean,
}

/**
 * Installation service
 */
class InstallationService {

	/**
	 * Checks the installation
	 */
	public check(): Promise<InstallationCheck> {
		store.commit('spinner/SHOW');
		return axios.get('/installation')
			.then((response: AxiosResponse) => {
				store.commit('installation/CHECKED');
				store.commit('spinner/HIDE');
				return response.data as InstallationCheck;
			});
	}

}

export default new InstallationService();
