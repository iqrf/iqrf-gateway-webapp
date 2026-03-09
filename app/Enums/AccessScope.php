<?php

/**
 * Copyright 2017-2026 IQRF Tech s.r.o.
 * Copyright 2019-2026 MICRORISC s.r.o.
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
declare(strict_types = 1);

namespace App\Enums;

use JsonSerializable;

/**
 * Access scope enum
 */
enum AccessScope: string implements JsonSerializable {

	// TODO - write description comment to each case
	case account_read = 'account:read';
	case account_write = 'account:write';
	case config_automaticUpgrades_read = 'config:automaticUpgrades:read';
	case config_automaticUpgrades_write = 'config:automaticUpgrades:write';
	case config_features_read = 'config:features:read';
	case config_features_write = 'config:features:write';
	case config_iqrfGatewayController_read = 'config:iqrfGatewayController:read';
	case config_iqrfGatewayController_write = 'config:iqrfGatewayController:write';
	case config_iqrfGatewayDaemon_read = 'config:iqrfGatewayDaemon:read';
	case config_iqrfGatewayDaemon_write = 'config:iqrfGatewayDaemon:write';
	case config_iqrfGatewayInfluxdbBridge_read = 'config:iqrfGatewayInfluxdbBridge:read';
	case config_iqrfGatewayInfluxdbBridge_write = 'config:iqrfGatewayInfluxdbBridge:write';
	case config_iqrfRepository_read = 'config:iqrfRepository:read';
	case config_iqrfRepository_write = 'config:iqrfRepository:write';
	case config_journal_read = 'config:journal:read';
	case config_journal_write = 'config:journal:write';
	case config_mailer_read = 'config:mailer:read';
	case config_mailer_write = 'config:mailer:write';
	case config_mender_read = 'config:mender:read';
	case config_mender_write = 'config:mender:write';
	case config_monit_read = 'config:monit:read';
	case config_monit_write = 'config:monit:write';
	case config_time_read = 'config:time:read';
	case config_time_write = 'config:time:write';
	case gateway_backup_execute = 'gateway:backup:execute';
	case gateway_diagnostic_read = 'gateway:diagnostic:read';
	case gateway_information_read = 'gateway:information:read';
	case gateway_information_write = 'gateway:information:write';
	case gateway_mender_execute = 'gateway:mender:execute';
	case gateway_power_execute = 'gateway:power:execute';
	case gateway_power_read = 'gateway:power:read';
	case gateway_service_execute = 'gateway:service:execute';
	case gateway_service_read = 'gateway:service:read';
	case gateway_version_read = 'gateway:version:read';
	case ipNetwork_physicalConnections_execute = 'ipNetwork:physicalConnections:execute';
	case ipNetwork_physicalConnections_read = 'ipNetwork:physicalConnections:read';
	case ipNetwork_physicalConnections_write = 'ipNetwork:physicalConnections:write';
	case ipNetwork_vpns_read = 'ipNetwork:vpns:read';
	case ipNetwork_vpns_write = 'ipNetwork:vpns:write';
	case iqrfNetwork_macros_read = 'iqrfNetwork:macros:read';
	case iqrfNetwork_trUpload_execute = 'iqrfNetwork:trUpload:execute';
	case security_apiKeys_read = 'security:apiKeys:read';
	case security_apiKeys_write = 'security:apiKeys:write';
	case security_certificates_read = 'security:certificates:read';
	case security_certificates_write = 'security:certificates:write';
	case security_shellUser_write = 'security:shellUser:write';
	case security_sshkeys_read = 'security:sshkeys:read';
	case security_sshkeys_write = 'security:sshkeys:write';
	case security_users_read = 'security:users:read';
	case security_users_write = 'security:users:write';

	/**
	 * Serializes access scope enum member into JSON
	 * @return string JSON-serialized access scope
	 */
	public function jsonSerialize(): string {
		return $this->value;
	}

}
