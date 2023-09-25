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

/**
 * Feature configuration
 */
export interface FeatureConfig {

	/**
	 * Feature enablement
	 */
	enabled: boolean;

	/**
	 * Path related to the feature
	 */
	path?: string;

	/**
	 * Feature URL
	 */
	url?: string;

	/**
	 * Gateway user name
	 */
	user?: string;
}

/**
 * Feature enum
 */
export enum Feature {

	/**
	 * apcupsd service feature
	 */
	apcupsd = 'apcupsd',

	/**
	 * Documentation feature
	 */
	docs = 'docs',

	/**
	 * Default gateway user password change password feature
	 */
	gatewayPassword = 'gatewayPass',

	/**
	 * Grafana dashboard feature
	 */
	grafana = 'grafana',

	/**
	 * iTemp service feature
	 */
	iTemp = 'iTemp',

	/**
	 * IQRF Gateway Controller feature
	 */
	iqrfGatewayController = 'iqrfGatewayController',

	/**
	 * IQRF Gateway Translator feature
	 */
	iqrfGatewayTranslator = 'iqrfGatewayTranslator',

	/**
	 * IQRF Repository Extension configuration feature
	 */
	iqrfRepository = 'iqrfRepository',

	/**
	 * Systemd journald configuration feature
	 */
	journal = 'journal',

	/**
	 * Mender client configuration feature
	 */
	mender = 'mender',

	/**
	 * Monit feature
	 */
	monit = 'monit',

	/**
	 * Network manager feature
	 */
	networkManager = 'networkManager',

	/**
	 * Node-RED feature
	 */
	nodeRed = 'nodeRed',

	/**
	 * Remount root filesystem endpoints feature
	 */
	remount = 'remount',

	/**
	 * SSH daemon manager feature
	 */
	ssh = 'ssh',

	/**
	 * supervisord dashboard feature
	 */
	supervisord = 'supervisord',

	/**
	 * IQRF TR native upload feature
	 */
	trUpload = 'trUpload',

	/**
	 * Unattended upgrades feature
	 */
	unattendedUpgrades = 'unattendedUpgrades',

	/**
	 * System updater feature
	 */
	updater = 'updater',

	/**
	 * IQRF Gateway Webapp version checker feature
	 */
	versionCheck = 'versionCheck',

}

/**
 * @type {Record<Feature, FeatureConfig>} Features
 */
export type Features = Record<Feature, FeatureConfig>;
