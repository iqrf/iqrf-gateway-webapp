/**
 * Copyright 2023-2026 MICRORISC s.r.o.
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
 * Access scope enum
 */
export enum AccessScope {
	account_read = 'account:read',
	account_write = 'account:write',
	config_automaticUpgrades_read = 'config:automaticUpgrades:read',
	config_automaticUpgrades_write = 'config:automaticUpgrades:write',
	config_features_read = 'config:features:read',
	config_features_write = 'config:features:write',
	config_iqrfGatewayController_read = 'config:iqrfGatewayController:read',
	config_iqrfGatewayController_write = 'config:iqrfGatewayController:write',
	config_iqrfGatewayDaemon_read = 'config:iqrfGatewayDaemon:read',
	config_iqrfGatewayDaemon_write = 'config:iqrfGatewayDaemon:write',
	config_iqrfGatewayInfluxdbBridge_read = 'config:iqrfGatewayInfluxdbBridge:read',
	config_iqrfGatewayInfluxdbBridge_write = 'config:iqrfGatewayInfluxdbBridge:write',
	config_iqrfRepository_read = 'config:iqrfRepository:read',
	config_iqrfRepository_write = 'config:iqrfRepository:write',
	config_journal_read = 'config:journal:read',
	config_journal_write = 'config:journal:write',
	config_mailer_read = 'config:mailer:read',
	config_mailer_write = 'config:mailer:write',
	config_mender_read = 'config:mender:read',
	config_mender_write = 'config:mender:write',
	config_monit_read = 'config:monit:read',
	config_monit_write = 'config:monit:write',
	config_time_read = 'config:time:read',
	config_time_write = 'config:time:write',
	gateway_backup_execute = 'gateway:backup:execute',
	gateway_diagnostic_read = 'gateway:diagnostic:read',
	gateway_information_read = 'gateway:information:read',
	gateway_information_write = 'gateway:information:write',
	gateway_mender_execute = 'gateway:mender:execute',
	gateway_power_execute = 'gateway:power:execute',
	gateway_power_read = 'gateway:power:read',
	gateway_service_execute = 'gateway:service:execute',
	gateway_service_read = 'gateway:service:read',
	gateway_version_read = 'gateway:version:read',
	ipNetwork_physicalConnections_execute = 'ipNetwork:physicalConnections:execute',
	ipNetwork_physicalConnections_read = 'ipNetwork:physicalConnections:read',
	ipNetwork_physicalConnections_write = 'ipNetwork:physicalConnections:write',
	ipNetwork_vpns_read = 'ipNetwork:vpns:read',
	ipNetwork_vpns_write = 'ipNetwork:vpns:write',
	iqrfNetwork_macros_read = 'iqrfNetwork:macros:read',
	iqrfNetwork_trUpload_execute = 'iqrfNetwork:trUpload:execute',
	security_apiKeys_read = 'security:apiKeys:read',
	security_apiKeys_write = 'security:apiKeys:write',
	security_certificates_read = 'security:certificates:read',
	security_certificates_write = 'security:certificates:write',
	security_shellUser_write = 'security:shellUser:write',
	security_sshkeys_read = 'security:sshkeys:read',
	security_sshkeys_write = 'security:sshkeys:write',
	security_users_read = 'security:users:read',
	security_users_write = 'security:users:write',
}
