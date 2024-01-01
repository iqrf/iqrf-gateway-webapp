/**
 * Copyright 2023-2024 MICRORISC s.r.o.
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

import type {AxiosResponse} from 'axios';

import {BaseService} from './BaseService';
import type {Feature, FeatureConfig, Features} from '../types';

/**
 * Feature service
 */
export class FeatureService extends BaseService {

	/**
	 * Fetches all features
	 * @return {Promise<Features>} Features
	 */
	public fetchAll(): Promise<Features> {
		return this.axiosInstance.get('/features')
			.then((response: AxiosResponse<Features>): Features => response.data);
	}


	/**
	 * Fetches feature configuration
	 * @param {Feature} feature Feature
	 * @return {Promise<FeatureConfig>} Feature configuration
	 */
	public getConfig(feature: Feature): Promise<FeatureConfig> {
		return this.axiosInstance.get(`/features/${feature.toString()}`)
			.then((response: AxiosResponse<FeatureConfig>): FeatureConfig => response.data);
	}

	/**
	 * Sets feature configuration
	 * @param {Feature} feature Feature
	 * @param {FeatureConfig} config Feature configuration
	 */
	public setConfig(feature: Feature, config: FeatureConfig): Promise<void> {
		return this.axiosInstance.put(`/features/${feature.toString()}`, config)
			.then((): void => {return;});

	}

}
