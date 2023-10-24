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

import {BaseService} from './BaseService';

import {IqrfGatewayControllerService} from './Config/IqrfGatewayControllerService';
import {IqrfGatewayDaemonService} from './Config/IqrfGatewayDaemonService';
import {IqrfRepositoryService} from './Config/IqrfRepositoryService';
import {MailerService} from './Config/MailerService';

export * from './Config/IqrfGatewayControllerService';
export * from './Config/IqrfGatewayDaemonService';
export * from './Config/IqrfRepositoryService';
export * from './Config/MailerService';

/**
 * Configuration services
 */
export class ConfigServices extends BaseService {

	/**
	 * Returns IQRF repository service
	 * @return {IqrfRepositoryService} IQRF repository service
	 */
	public getIqrfRepositoryService(): IqrfRepositoryService {
		return new IqrfRepositoryService(this.apiClient);
	}

	/**
	 * Returns IQRF Gateway Controller configuration service
	 * @return {IqrfGatewayControllerService} IQRF Gateway Controller configuration service
	 */
	public getIqrfGatewayControllerService(): IqrfGatewayControllerService {
		return new IqrfGatewayControllerService(this.apiClient);
	}

	/**
	 * Returns IQRF Gateway Daemon configuration service
	 * @returns {IqrfGatewayDaemonService} IQRF Gateway Daemon configuration service
	 */
	public getIqrfGatewayDaemonService(): IqrfGatewayDaemonService {
		return new IqrfGatewayDaemonService(this.apiClient);
	}

	/**
	 * Returns mailer service
	 * @return {MailerService} Mailer service
	 */
	public getMailerService(): MailerService {
		return new MailerService(this.apiClient);
	}
}
