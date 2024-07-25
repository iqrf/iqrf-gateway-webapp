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

import { BaseService } from '../BaseService';

import { AptService } from './AptService';
import { IqrfGatewayControllerService } from './IqrfGatewayControllerService';
import { IqrfGatewayDaemonService } from './IqrfGatewayDaemonService';
import { IqrfGatewayInfluxdbBridgeService } from './IqrfGatewayInfluxdbBridgeService';
import { IqrfRepositoryService } from './IqrfRepositoryService';
import { JournalService } from './JournalService';
import { MailerService } from './MailerService';
import { MenderService } from './MenderService';
import { MonitService } from './MonitService';

export * from './AptService';
export * from './IqrfGatewayControllerService';
export * from './IqrfGatewayDaemonService';
export * from './IqrfGatewayInfluxdbBridgeService';
export * from './IqrfRepositoryService';
export * from './JournalService';
export * from './MailerService';
export * from './MenderService';
export * from './MonitService';

/**
 * Configuration services
 */
export class ConfigServices extends BaseService {

	/**
	 * Returns APT service
	 * @return {AptService} APT service
	 */
	public getAptService(): AptService {
		return new AptService(this.apiClient);
	}

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
	 * @return {IqrfGatewayDaemonService} IQRF Gateway Daemon configuration service
	 */
	public getIqrfGatewayDaemonService(): IqrfGatewayDaemonService {
		return new IqrfGatewayDaemonService(this.apiClient);
	}

	/**
	 * Returns IQRF Gateway InfluxDB Bridge configuration service
	 * @return {IqrfGatewayInfluxdbBridgeService} IQRF Gateway InfluxDB Bridge configuration service
	 */
	public getIqrfGatewayInfluxdbBridgeService(): IqrfGatewayInfluxdbBridgeService {
		return new IqrfGatewayInfluxdbBridgeService(this.apiClient);
	}

	/**
	 * Returns journal service
	 * @return {JournalService} Journal service
	 */
	public getJournalService(): JournalService {
		return new JournalService(this.apiClient);
	}

	/**
	 * Returns mailer service
	 * @return {MailerService} Mailer service
	 */
	public getMailerService(): MailerService {
		return new MailerService(this.apiClient);
	}

	/**
	 * Returns Mender service
	 * @return {MenderService} Mender service
	 */
	public getMenderService(): MenderService {
		return new MenderService(this.apiClient);
	}

	/**
	 * Returns monit service
	 * @return {MonitService} Monit service
	 */
	public getMonitService(): MonitService {
		return new MonitService(this.apiClient);
	}

}
