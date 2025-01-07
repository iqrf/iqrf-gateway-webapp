/**
 * Copyright 2023-2025 MICRORISC s.r.o.
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

import { type AxiosResponse } from 'axios';

import { type Feature, type FeatureConfig, type Features } from '../types';

import { BaseService } from './BaseService';

/**
 * Feature service
 */
export class FeatureService extends BaseService {

	/**
	 * Retrieve all features
	 * @return {Promise<Features>} Features
	 */
	public async list(): Promise<Features> {
		const response: AxiosResponse<Features> =
			await this.axiosInstance.get('/features');
		return response.data;
	}


	/**
	 * Retrieve feature configuration
	 * @param {Feature} feature Feature
	 * @return {Promise<FeatureConfig>} Feature configuration
	 */
	public async getConfig(feature: Feature): Promise<FeatureConfig> {
		const response: AxiosResponse<FeatureConfig> =
			await this.axiosInstance.get(`/features/${feature.toString()}`);
		return response.data;
	}

	/**
	 * Update feature configuration
	 * @param {Feature} feature Feature
	 * @param {FeatureConfig} config Feature configuration
	 */
	public async updateConfig(feature: Feature, config: FeatureConfig): Promise<void> {
		await this.axiosInstance.put(`/features/${feature.toString()}`, config);
	}

}
