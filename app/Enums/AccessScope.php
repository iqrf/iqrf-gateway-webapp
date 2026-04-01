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

use DomainException;
use JsonSerializable;

/**
 * Access scope enum
 * Semantic:
 * - Read - allows user to read configuration / data from endpoint
 * - Write - allows user to write / update data on endpoint
 * - Execute - allows user to run some action, that is exposed by endpoint
 *             (for example enable service, create and download backup file, etc.)
 */
enum AccessScope: string implements JsonSerializable {

	/**
	 * Used for User account access
	 */
	case account_read = 'account:read';
	case account_write = 'account:write';

	/**
	 * Used for APT configuration endpoints
	 */
	case config_automaticUpgrades_read = 'config:automaticUpgrades:read';
	case config_automaticUpgrades_write = 'config:automaticUpgrades:write';

	/**
	 * Used for controlling access to optional features endpoints
	 */
	case config_features_read = 'config:features:read';
	case config_features_write = 'config:features:write';

	/**
	 * Used for IQRF Gateway controller endpoints
	 */
	case config_iqrfGatewayController_read = 'config:iqrfGatewayController:read';
	case config_iqrfGatewayController_write = 'config:iqrfGatewayController:write';

	/**
	 * Used for
	 * - Cloud service configurations (AWS, Azure, etc.)
	 * - IQRF Daemon configuration endpoints
	 * - Mappings configuration endpoints
	 * - Scheduler configuration endpoints
	 * - IQRF interfaces endpoint
	 */
	case config_iqrfGatewayDaemon_read = 'config:iqrfGatewayDaemon:read';
	case config_iqrfGatewayDaemon_write = 'config:iqrfGatewayDaemon:write';

	/**
	 * Used for InfluxDB bridge configuration endpoints
	 */
	case config_iqrfGatewayInfluxdbBridge_read = 'config:iqrfGatewayInfluxdbBridge:read';
	case config_iqrfGatewayInfluxdbBridge_write = 'config:iqrfGatewayInfluxdbBridge:write';

	/**
	 * Used for IQRF Repository configuration endpoints
	 */
	case config_iqrfRepository_read = 'config:iqrfRepository:read';
	case config_iqrfRepository_write = 'config:iqrfRepository:write';

	/**
	 * Used for Journal configuration endpoints
	 */
	case config_journal_read = 'config:journal:read';
	case config_journal_write = 'config:journal:write';

	/**
	 * Used for Mailer configuration endpoints
	 */
	case config_mailer_read = 'config:mailer:read';
	case config_mailer_write = 'config:mailer:write';

	/**
	 * Used for Mender configuration endpoints
	 */
	case config_mender_read = 'config:mender:read';
	case config_mender_write = 'config:mender:write';

	/**
	 * Used for Monit configuration endpoints
	 */
	case config_monit_read = 'config:monit:read';
	case config_monit_write = 'config:monit:write';

	/**
	 * Used for Time configuration and information endpoints
	 */
	case config_time_read = 'config:time:read';
	case config_time_write = 'config:time:write';

	/**
	 * Used for Translator configuration endpoints
	 */
	case config_translator_read = 'config:translator:read';
	case config_translator_write = 'config:translator:write';

	/**
	 * Used for creating, downloading and restoring backups
	 */
	case gateway_backup_execute = 'gateway:backup:execute';

	/**
	 * Used for
	 * - Diagnostic endpoint
	 * - Endpoint that returns journal records
	 * - Log endpoints
	 */
	case gateway_diagnostic_read = 'gateway:diagnostic:read';

	/**
	 * Used for
	 * - Hostname endpoint
	 * - Gateway information endpoints
	 */
	case gateway_information_read = 'gateway:information:read';
	case gateway_information_write = 'gateway:information:write';

	/**
	 * Used for access to mender update and artifact installation endpoints
	 */
	case gateway_mender_execute = 'gateway:mender:execute';

	/**
	 * Used for Power management endpoints
	 */
	case gateway_power_execute = 'gateway:power:execute';
	case gateway_power_read = 'gateway:power:read';

	/**
	 * Used for controlling access to endpoints for enabling and disabling system services
	 */
	case gateway_service_execute = 'gateway:service:execute';
	case gateway_service_read = 'gateway:service:read';

	/**
	 * Used for access control to version endpoints
	 */
	case gateway_version_read = 'gateway:version:read';

	/**
	 * Used for working with physical interface configurations and connecting to IP network
	 * (includes setting up IP addresses, scanning for wifi and modems, etc.)
	 */
	case ipNetwork_physicalConnections_execute = 'ipNetwork:physicalConnections:execute';
	case ipNetwork_physicalConnections_read = 'ipNetwork:physicalConnections:read';
	case ipNetwork_physicalConnections_write = 'ipNetwork:physicalConnections:write';

	/**
	 * Used for managing VPN connections
	 */
	case ipNetwork_vpns_execute = 'ipNetwork:vpns:execute';
	case ipNetwork_vpns_read = 'ipNetwork:vpns:read';
	case ipNetwork_vpns_write = 'ipNetwork:vpns:write';

	/**
	 * Used for access to IQRF IDE macros endpoint
	 */
	case iqrfNetwork_macros_read = 'iqrfNetwork:macros:read';

	/**
	 * Used for
	 * - IQRF OS Patches and updates endpoints
	 * - Upload endpoints
	 */
	case iqrfNetwork_trUpload_execute = 'iqrfNetwork:trUpload:execute';

	/**
	 * Used for API key management
	 */
	case security_apiKeys_read = 'security:apiKeys:read';
	case security_apiKeys_write = 'security:apiKeys:write';

	/**
	 * Used for TLS Certificate endpoints
	 */
	case security_certificates_read = 'security:certificates:read';
	case security_certificates_write = 'security:certificates:write';

	/**
	 * Used to control access to roles (user and access token roles)
	 */
	case security_role_read = 'security:role:read';
	case security_role_write = 'security:role:write';

	/**
	 * Used to control access to endpoint for setting password to shell user
	 * (user accessible from terminal when SSH access is enabled)
	 */
	case security_shellUser_write = 'security:shellUser:write';

	/**
	 * Used for SSH keys management endpoints
	 */
	case security_sshkeys_read = 'security:sshkeys:read';
	case security_sshkeys_write = 'security:sshkeys:write';

	/**
	 * Used for User management endpoints (App users)
	 */
	case security_users_read = 'security:users:read';
	case security_users_write = 'security:users:write';

	/**
	 * Parses access scope from strings corresponding to the access scope
	 * @param string $scope Access scope string reprezentation
	 * @return AccessScope Access scope coresponding to given string
	 * @throws DomainException when given string does not corespond to any access scope
	 */
	public static function parseScopeFromString(string $scope): AccessScope {
		$as = AccessScope::tryFrom($scope);
		if ($as === null) {
			throw new DomainException('Invalid access scope ' . $scope . '!');
		}
		return $as;
	}

	/**
	 * Parses scopes from array of strings corresponding to the access scopes
	 * @param array<string> $scopes Array of access scopes given by string reprezentations
	 * @return array<AccessScope> Array of parsed access scopes
	 * @throws DomainException when given string does not corespond to any access scope
	 */
	public static function parseScopesFromStringArray(array $scopes): array {
		return array_map(
			self::parseScopeFromString(...),
			$scopes
		);
	}

	/**
	 * Serializes access scope enum member into JSON
	 * @return string JSON-serialized access scope
	 */
	public function jsonSerialize(): string {
		return $this->value;
	}

}
