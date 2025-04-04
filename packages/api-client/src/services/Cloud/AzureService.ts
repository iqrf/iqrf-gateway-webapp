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

import { type AzureIotHubConfig } from '../../types/Cloud';
import { BaseService } from '../BaseService';

/**
 * Azure IoT Hub service
 */
export class AzureService extends BaseService {

	/**
	 * Creates a new Azure IoT Hub MQTT instance
	 * @param {AzureIotHubConfig} config Azure IoT Hub configuration
	 */
	public async createMqttInstance(config: AzureIotHubConfig): Promise<void> {
		await this.axiosInstance.post('/clouds/azure', config);
	}

}
