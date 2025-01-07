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

import { type IqrfGatewayTranslatorConfig } from '../../types/Config';
import { BaseService } from '../BaseService';

/**
 * IQRF Gateway Translator configuration service
 */
export class IqrfGatewayTranslatorService extends BaseService {

	/**
	 * Retrieves IQRF Gateway Translator configuration
	 * @return {Promise<IqrfGatewayTranslatorConfig>} IQRF Gateway Translator configuration
	 */
	public async getConfig(): Promise<IqrfGatewayTranslatorConfig> {
		const response: AxiosResponse<IqrfGatewayTranslatorConfig> =
			await this.axiosInstance.get('/config/translator');
		return response.data;
	}

	/**
	 * Updates IQRF Gateway Translator configuration
	 * @param {IqrfGatewayTranslatorConfig} config IQRF Gateway Translator configuration
	 */
	public async updateConfig(config: IqrfGatewayTranslatorConfig): Promise<void> {
		await this.axiosInstance.put('/config/translator', config);
	}

}
