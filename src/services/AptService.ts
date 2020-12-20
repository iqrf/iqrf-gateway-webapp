import axios, {AxiosResponse} from 'axios';
import { authorizationHeader } from '../helpers/authorizationHeader';

/**
 * APT configuration
 */
export interface AptConfiguration {
	/**
	 * Enable automatic upgrades
	 */
	'APT::Periodic::Enable': string
}

export interface AptConfigurationExtended extends AptConfiguration {
		/**
	 * Package list update interval
	 */
	'APT::Periodic::Update-Package-Lists': string

	/**
	 * Package upgrade interval
	 */
	'APT::Periodic::Unattended-Upgrade': string

	/**
	 * Unnecessary package removal interval
	 */
	'APT::Periodic::AutocleanInterval': string

	/**
	 * Reboot on kernel updates
	 */
	'Unattended-Upgrade::Automatic-Reboot': string
}

/**
 * APT configuration service
 */
class AptService {

	/**
	 * Retrieves APT configuration
	 */
	read(): Promise<AxiosResponse> {
		return axios.get('/config/apt', {headers: authorizationHeader()});
	}

	/**
	 * Sets APT configuration
	 * @param configuration APT configuration
	 */
	write(configuration: AptConfiguration|AptConfigurationExtended): Promise<AxiosResponse> {
		return axios.put('/config/apt', configuration, {headers: authorizationHeader()});
	}
}

export default new AptService();
