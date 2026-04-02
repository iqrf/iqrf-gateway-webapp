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
 * Feature
 */
export interface Feature {

	/**
	 * Feature enablement
	 */
	enabled: boolean;

	/**
	 * Feature URL
	 */
	url?: string;
}

/**
 * Gateway password feature
 */
export interface GatewayPasswordFeature extends Feature {

	/**
	 * Gateway user name
	 */
	user: string
}

/**
 * Features
 */
export type Features = Record<string, Feature>;

/**
 * Optional feature service
 */
class FeatureService {

	/**
	 * Fetch all features
	 */
	fetchAll(): Promise<Features> {
		return axios.get('features', {headers: authorizationHeader()})
			.then((response: AxiosResponse) => {
				return response.data as Features;
			});
	}

}

export default new FeatureService();
