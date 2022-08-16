/**
 * Copyright 2017-2021 IQRF Tech s.r.o.
 * Copyright 2019-2021 MICRORISC s.r.o.
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
import i18n from '@/plugins/i18n';
import store from '@/store';

/**
 * Installation check interface
 */
export interface InstallationCheck {
	allMigrationsExecuted: boolean,
	hasUsers?: boolean,
	phpModules: InstallationCheckPhp,
	sudo?: InstallationCheckSudo,
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
		store.commit('spinner/SHOW');
		store.commit('spinner/UPDATE_TEXT', i18n.t('install.messages.check').toString());
		return axios.get('/installation')
			.then((response: AxiosResponse) => {
				store.commit('installation/CHECKED');
				store.commit('spinner/HIDE');
				return response.data as InstallationCheck;
			});
	}

}

export default new InstallationService();
