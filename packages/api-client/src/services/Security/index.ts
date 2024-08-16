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

import { ApiKeyService } from './ApiKeyService';
import { CertificateService } from './CertificateService';
import { SshKeyService } from './SshKeyService';
import { UserService } from './UserService';

export * from './ApiKeyService';
export * from './CertificateService';
export * from './SshKeyService';
export * from './UserService';

/**
 * Security services
 */
export class SecurityServices extends BaseService {

	/**
	 * Returns API key service
	 * @return {ApiKeyService} API key service
	 */
	public getApiKeyService(): ApiKeyService {
		return new ApiKeyService(this.apiClient);
	}

	/**
	 * Returns TLS certificate service
	 * @return {CertificateService} TLS certificate service
	 */
	public getCertificateService(): CertificateService {
		return new CertificateService(this.apiClient);
	}

	/**
	 * Returns SSH key service
	 * @return {SshKeyService} SSH key service
	 */
	public getSshKeyService(): SshKeyService {
		return new SshKeyService(this.apiClient);
	}

	/**
	 * Returns user management service
	 * @return {UserService} User management service
	 */
	public getUserService(): UserService {
		return new UserService(this.apiClient);
	}

}
