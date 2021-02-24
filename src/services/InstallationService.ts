import axios, {AxiosResponse} from 'axios';

/**
 * Installation check interface
 */
export interface InstallationCheck {
	allMigrationsExecuted: boolean,
	hasUsers?: boolean,
	phpModules: InstallationCheckPhpModules,
	sudo: InstallationCheckSudo,
}

/**
 * Installation check php extensions interface
 */
export interface InstallationCheckPhpModules {
	allExtensionsLoaded: boolean,
	missing?: Array<string>,
}

/**
 * Installation check sudo interface
 */
export interface InstallationCheckSudo {
	exists: boolean,
	webappSudo: boolean,
}

/**
 * Installation service
 */
class InstallationService {

	/**
	 * Checks the installation
	 */
	public check(): Promise<InstallationCheck> {
		return axios.get('/installation')
			.then((response: AxiosResponse) => {
				return response.data as InstallationCheck;
			});
	}

}

export default new InstallationService();
