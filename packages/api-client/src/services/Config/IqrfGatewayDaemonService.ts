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

import type { AxiosResponse } from 'axios';

import { BaseService } from '../BaseService';
import {
	type IqrfGatewayDaemonComponent,
	type IqrfGatewayDaemonComponentInstanceConfiguration,
	type IqrfGatewayDaemonComponentName,
} from '../../types/Config';

/**
 * IQRF Gateway Daemon configuration service
 */
export class IqrfGatewayDaemonService extends BaseService {

	/**
	 * Retrieves IQRF Gateway Daemon component configuration and instances
	 * @template C Component name
	 * @param {C} component Component name
	 * @return {Promise<IqrfGatewayDaemonComponent<C>>} IQRF Gateway Daemon component configuration with instances
	 */
	public getComponent<C extends IqrfGatewayDaemonComponentName>(
		component: C,
	): Promise<IqrfGatewayDaemonComponent<C>> {
		return this.axiosInstance.get(`/config/daemon/${encodeURIComponent(component)}`)
			.then((response: AxiosResponse<IqrfGatewayDaemonComponent<C>>): IqrfGatewayDaemonComponent<C> => response.data);
	}

	/**
	 * Updates the component instance configuration
	 * @template C Component name
	 * @param {C} component Daemon component name
	 * @param {string} instance Daemon component instance name
	 * @param {IqrfGatewayDaemonComponentInstanceConfiguration<C>} configuration Daemon component instance configuration
	 */
	public updateInstance<C extends IqrfGatewayDaemonComponentName>(
		component: C,
		instance: string,
		configuration: IqrfGatewayDaemonComponentInstanceConfiguration<C>,
	): Promise<void> {
		return this.axiosInstance.put(`/config/daemon/${encodeURIComponent(component)}/${encodeURIComponent(instance)}`, configuration)
			.then((): void => {return;});
	}
}
