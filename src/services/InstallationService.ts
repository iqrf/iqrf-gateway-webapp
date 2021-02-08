import axios, {AxiosResponse} from 'axios';

/**
 * Installation check
 */
export interface InstallationCheck {
	allMigrationsExecuted: boolean,
	hasUsers?: boolean,
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
