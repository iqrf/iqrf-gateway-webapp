import axios from 'axios';
import {authorizationHeader} from '../helpers/authorizationHeader';

/**
 * IQRF Gateway Translator service
 */
class TranslatorConfigService {
	/**
	 * Retrieves the translator configuration
	 * @returns {Promise<AxiosResponse<<any>>}
	 */
	getConfig() {
		return axios.get('translatorConfig', {headers: authorizationHeader()});
	}

	/**
	 * Saves new translator configuration
	 * @returns {Promise<AxiosResponse<<any>>}
	 */
	saveConfig(config) {
		return axios.put('translatorConfig', config, {headers: authorizationHeader()});
	}
}

export default new TranslatorConfigService();
