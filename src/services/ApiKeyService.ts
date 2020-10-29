import axios, {AxiosResponse} from 'axios';
import {authorizationHeader} from '../helpers/authorizationHeader';

/**
 * API key service
 */
class ApiKeyService {
	/**
	 * Retrieves list of API keys
	 */
	getApiKeys(): Promise<AxiosResponse> {
		return axios.get('apiKeys', {headers: authorizationHeader()});
	}

	/**
	 * Adds a new API key
	 * @param keyData new API key metadata
	 */
	addApiKey(keyData: Record<string, unknown>): Promise<AxiosResponse> {
		return axios.post('apiKeys', keyData, {headers: authorizationHeader()});
	}

	/**
	 * Retrieves API key specified by ID
	 * @param keyId API key ID
	 */
	getApiKey(keyId: number): Promise<AxiosResponse> {
		return axios.get('apiKeys/' + keyId, {headers: authorizationHeader()});
	}

	/**
	 * Updates metadata of API key specified by ID
	 * @param keyId API key ID
	 * @param keyData API key metadata
	 */
	editApiKey(keyId: number, keyData: Record<string, unknown>): Promise<AxiosResponse> {
		delete keyData.id;
		return axios.put('apiKeys/' + keyId, keyData, {headers: authorizationHeader()});
	}

	/**
	 * Removes API key specified by ID
	 * @param keyId API key ID
	 */
	deleteApiKey(keyId: number): Promise<AxiosResponse> {
		return axios.delete('apiKeys/' + keyId, {headers: authorizationHeader()});
	}
}

export default new ApiKeyService();
