/**
 * Copyright 2023 MICRORISC s.r.o.
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

import {BaseService} from '../BaseService';

import {AwsService} from './AwsService';
import {AzureService} from './AzureService';

export * from './AwsService';

/**
 * Cloud services
 */
export class CloudServices extends BaseService {

	/**
	 * Returns AWS IoT service
	 * @return {AwsService} AWS IoT service
	 */
	public getAwsService(): AwsService {
		return new AwsService(this.apiClient);
	}

	/**
	 * Returns Azure IoT Hub service
	 * @return {AzureService} Azure IoT Hub service
	 */
	public getAzureService(): AzureService {
		return new AzureService(this.apiClient);
	}

}
