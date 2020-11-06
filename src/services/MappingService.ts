import axios, {AxiosResponse} from 'axios';
import {authorizationHeader} from '../helpers/authorizationHeader';
import { IMapping } from '../interfaces/mappings';

/**
 * Mapping service
 */
class MappingService {
	/**
	 * Adds a new mapping to mapping database
	 * @param {IMapping} mapping Mapping data
	 */
	addMapping(mapping: IMapping): Promise<AxiosResponse> {
		return axios.post('mappings', mapping, {headers: authorizationHeader()});
	}

	/**
	 * Edits an existing mapping in mapping database
	 * @param {number} mappingId Mapping ID
	 * @param {IMapping} mapping Mapping data
	 */
	editMapping(mappingId: number, mapping: IMapping): Promise<AxiosResponse> {
		return axios.put('mappings/' + mappingId, mapping, {headers: authorizationHeader()});
	}

	/**
	 * Retrieves list of mappings
	 */
	getMappings(): Promise<AxiosResponse> {
		return axios.get('mappings', {headers: authorizationHeader()});
	}

	/**
	 * Retrieves a mapping specified by ID
	 * @param mappingId Mapping ID
	 */
	getMapping(mappingId: number): Promise<AxiosResponse> {
		return axios.get('mappings/' + mappingId, {headers: authorizationHeader()});
	}

	/**
	 * Removes a mapping specified by ID from mappings database
	 * @param mappingId Mapping ID
	 */
	removeMapping(mappingId: number): Promise<AxiosResponse> {
		return axios.delete('mappings/' + mappingId, {headers: authorizationHeader()});
	}
}

export default new MappingService();