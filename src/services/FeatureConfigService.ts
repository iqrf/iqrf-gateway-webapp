/**
 * Copyright 2017-2025 IQRF Tech s.r.o.
 * Copyright 2019-2025 MICRORISC s.r.o.
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
import axios, {AxiosResponse} from 'axios';
import {authorizationHeader} from '@/helpers/authorizationHeader';

/**
 * Feature configuration service
 */
class FeatureConfigService {	
	/**
	 * Retrieves feature configuration
	 * @param featureName feature name
	 */
	getConfig(featureName: string): Promise<AxiosResponse> {
		return axios.get('config/' + featureName, {headers: authorizationHeader()});
	}

	/**
	 * Saves new feature configuration
	 * @param featureName feature name
	 * @param config new feature configuration
	 */
	saveConfig(featureName: string, config: any): Promise<AxiosResponse> {
		return axios.put('config/' + featureName, config, {headers: authorizationHeader()});
	}
}

export default new FeatureConfigService();
