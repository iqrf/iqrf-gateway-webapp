import axios, {AxiosResponse} from 'axios';

/**
 * Installation check
 */
export interface InstallationCheck {
	allMigrationsExecuted: boolean,
	hasUsers?: boolean,
}

/**
 * Root password interface
 */
export interface RootPassword {
	password: string,
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

	/**
	 * Sets gateway root password
	 * @param {RootPassword} data New root gateway password
	 */
	public setRootPass(data: RootPassword): Promise<AxiosResponse> {
		return axios.put('/installation/rootpass', data);
	}

}

export default new InstallationService();
