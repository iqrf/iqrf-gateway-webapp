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

import { type AwsMqttConfig } from '../../types/Cloud';
import { BaseService } from '../BaseService';

/**
 * Amazon Web Services IoT service
 */
export class AwsService extends BaseService {

	/**
	 * Creates a new AWS IoT MQTT instance
	 * @param {AwsMqttConfig} config AWS IoT configuration
	 */
	public createMqttInstance(config: AwsMqttConfig): Promise<void> {
		if (!config.certificate || !config.privateKey) {
			throw new Error('Certificate and private key must be set');
		}
		const formData: FormData = new FormData();
		formData.append('endpoint', config.endpoint);
		formData.append('certificate', config.certificate as Blob);
		formData.append('privateKey', config.privateKey as Blob);
		return this.axiosInstance.post('/clouds/aws', formData)
			.then((): void => {return;});
	}

}
