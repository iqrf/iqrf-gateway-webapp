/**
 * Copyright 2017-2023 IQRF Tech s.r.o.
 * Copyright 2019-2023 MICRORISC s.r.o.
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

/**
 * Installation check interface
 */
export interface InstallationCheck {
	allMigrationsExecuted: boolean,
	dependencies: Array<InstallationCheckDependency>
	hasUsers?: boolean,
	phpModules: InstallationCheckPhp,
	sudo?: InstallationCheckSudo,
}

export interface InstallationCheckDependency {
	command: string,
	critical: boolean,
	package: string,
	feature?: string,
}

/**
 * Installation check php extensions interface
 */
export interface InstallationCheckPhp {
	allExtensionsLoaded: boolean,
	missing?: InstallationCheckPhpMissing,
}

export interface InstallationCheckPhpMissing {
	extensions: Array<string>,
	packages?: Array<string>,
}

/**
 * Installation check sudo interface
 */
export interface InstallationCheckSudo {
	user: string
	exists: boolean,
	userSudo: boolean,
}

/**
 * Installation service
 */
class InstallationService {

	/**
	 * Checks the installation
	 */
	public check(): Promise<InstallationCheck> {
		return axios.get('/installation')
			.then((response: AxiosResponse) => {
				return response.data as InstallationCheck;
			});
	}

}

export default new InstallationService();
