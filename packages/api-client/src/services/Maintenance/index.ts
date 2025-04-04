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

import { BaseService } from '../BaseService';

import { BackupService } from './BackupService';
import { MenderService } from './MenderService';

export * from './BackupService';
export * from './MenderService';

/**
 * Maintenance services
 */
export class MaintenanceServices extends BaseService {

	/**
	 * Returns backup service
	 * @return {BackupService} Backup service
	 */
	public getBackupService(): BackupService {
		return new BackupService(this.apiClient);
	}

	/**
	 * Returns Mender service
	 * @return {MenderService} Mender service
	 */
	public getMenderService(): MenderService {
		return new MenderService(this.apiClient);
	}

}
