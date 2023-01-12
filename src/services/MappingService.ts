/**
 * Copyright 2017-2021 IQRF Tech s.r.o.
 * Copyright 2019-2021 MICRORISC s.r.o.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */
import {authorizationHeader} from '@/helpers/authorizationHeader';
import {MappingType} from '@/enums/Config/ConfigurationProfiles';

import axios, {AxiosResponse} from 'axios';
import {IMapping} from '@/interfaces/Config/Mapping';

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
	getMappings(interfaceType: MappingType|null = null): Promise<Array<IMapping>> {
		const params = interfaceType === null ? {} : {interface: interfaceType};
		return axios.get('mappings', {headers: authorizationHeader(), params})
			.then((response: AxiosResponse) => (response.data as Array<IMapping>));
	}

	/**
	 * Retrieves a mapping specified by ID
	 * @param mappingId Mapping ID
	 */
	getMapping(mappingId: number): Promise<IMapping> {
		return axios.get('mappings/' + mappingId, {headers: authorizationHeader()})
			.then((response: AxiosResponse) => (response.data as IMapping));
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
