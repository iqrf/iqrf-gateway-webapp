/**
 * Copyright 2017-2022 IQRF Tech s.r.o.
 * Copyright 2019-2022 MICRORISC s.r.o.
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
/**
 * IQRF Repository component instance interface
 */
export interface IIqrfRepository {
	/**
	 * Component name
	 */
	component: string

	/**
	 * Component instance name
	 */
	instance: string

	/**
	 * Repository URL
	 */
	urlRepo: string

	/**
	 * Check period in minutes
	 */
	checkPeriodInMinutes: number

	/**
	 * Download date if repository cache is empty?
	 */
	downloadIfRepoCacheEmpty: boolean
}

/**
 * IQRF repository access configuration
 */
export interface IIqrfRepositoryConfig {
	/**
	 * Repository API endpoint
	 */
	apiEndpoint: string

	/**
	 * Repository credentials
	 */
	credentials: IIqrfRepositoryCredentials
}

/**
 * IQRF Repository credentials
 */
export interface IIqrfRepositoryCredentials {
	/**
	 * Username
	 */
	username: string|null

	/**
	 * Password
	 */
	password: string|null
}
