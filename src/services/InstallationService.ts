import axios, {AxiosResponse} from 'axios';

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
		return axios.get('/installation')
			.then((response: AxiosResponse) => {
				return response.data as InstallationCheck;
			});
	}

}

export default new InstallationService();
