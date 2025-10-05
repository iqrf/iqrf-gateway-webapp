/**
 * Copyright 2017-2025 IQRF Tech s.r.o.
 * Copyright 2019-2025 MICRORISC s.r.o.
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
import { type Product } from '@iqrf/iqrf-repository-client/types';
import { mdiCheck, mdiCheckCircle, mdiCloseCircle, mdiHome, mdiSignalCellularOutline } from '@mdi/js';

import i18n from '@/plugins/i18n';
import { type DbDeviceData, type DbSensorData } from '@/types/DaemonApi/iqrfDb';

/**
 * Standard device object
 */
class StandardDevice {
	/**
	 * Device address
	 */
	private readonly address: number;

	/**
	 * Device module ID
	 */
	private readonly mid: number;

	/**
	 * Device hwpid
	 */
	private readonly hwpid: number;

	/**
	 * Device hwpid version
	 */
	private readonly hwpidVer: number;

	/**
	 * Device DPA version
	 */
	private dpa: number;

	/**
	 * Device OS build
	 */
	private osBuild: number;

	/**
	 * Device OS version
	 */
	private osVersion: string;

	/**
	 * Is device discovered?
	 */
	private discovered: boolean;

	/**
	 * Is device online?
	 */
	private online = false;

	/**
	 * Array of implemented standard sensors
	 */
	private sensors: DbSensorData[] = [];

	/**
	 * Array of implemented binary outputs
	 */
	private binouts = 0;

	/**
	 * Indicates that device implements light standard
	 */
	private light = false;

	/**
	 * Show device details
	 */
	public showDetails = false;

	/**
	 * Product details
	 */
	private product: Product;

	/**
	 * Standard supported icon
	 */
	private readonly standardSupported = mdiCheckCircle;

	/**
	 * Standard unsupported icon
	 */
	private readonly standardUnsupported = mdiCloseCircle;

	/**
	 * Constructor
	 * @param {DbDeviceData} data Device data
	 */
	public constructor(data: DbDeviceData) {
		this.address = data.address;
		this.mid = data.mid;
		this.hwpid = data.hwpid;
		this.hwpidVer = data.hwpidVersion;
		this.dpa = data.dpa;
		this.osBuild = data.osBuild;
		this.osVersion = data.osVersion;
		this.discovered = data.discovered;
		this.product = {
			name: 'Unknown',
			hwpid: this.hwpid,
			manufacturerId: -1,
			companyName: 'Unknown',
			homePage: '',
			picture: '',
			rfModes: {
				LP: false,
				STD: false,
			},
			metadata: null,
		};
		this.binouts = data.binouts?.count ?? 0;
	}

	/**
	 * Returns device address
	 * @return {number} Device address
	 */
	public getAddress(): number {
		return this.address;
	}

	/**
	 * Returns device MID
	 * @return {number} Device MID
	 */
	public getMid(): number {
		return this.mid;
	}

	/**
	 * Returns device mid as hex string
	 * @return {string} Device MID hex
	 */
	public getMidHex(): string {
		return this.mid.toString(16).padStart(8, '0').toUpperCase();
	}

	/**
	 * Returns device HWPID
	 * @return {number} Device HWPID
	 */
	public getHwpid(): number {
		return this.hwpid;
	}

	/**
	 * Returns device HWPID as hex string
	 * @return {string} Device HWPID hex
	 */
	public getHwpidHex(): string {
		return this.hwpid.toString(16).padStart(4, '0').toUpperCase();
	}

	/**
	 * Returns device HWPID version
	 * @return {number} Device HWPID version
	 */
	public getHwpidVer(): number {
		return this.hwpidVer;
	}

	/**
	 * Returns formatted device DPA version
	 * @return {string} Device DPA version
	 */
	public getDpa(): string {
		const major = (this.dpa >> 8).toString(16);
		const minor = (this.dpa & 0xFF).toString(16).padStart(2, '0');
		return `${major}.${minor}`;
	}

	/**
	 * Returns device OS build
	 * @return {string} Device OS build
	 */
	public getOsBuild(): string {
		return this.osBuild.toString(16).padStart(4, '0').toUpperCase();
	}

	/**
	 * Sets IQRF OS version
	 * @param {string} version IQRF OS version
	 */
	public setOsVersion(version: string): void {
		this.osVersion = version;
	}

	/**
	 * Returns device OS
	 * @return {string} Device OS
	 */
	public getOs(): string {
		const build = this.getOsBuild();
		if (!this.osVersion || this.osVersion === '') {
			return `${i18n.global.t('common.labels.notAvailable')} (${build})`;
		}
		return `${this.osVersion} (${build})`;
	}

	/**
	 * Sets product information
	 * @param {Product} product Product information
	 */
	public setProduct(product: Product|undefined): void {
		if (product === undefined) {
			return;
		}
		this.product = product;
	}

	/**
	 * Returns product name
	 * @return {string} Product name
	 */
	public getProductName(): string {
		return this.product.name;
	}

	/**
	 * Returns product manufacturer
	 * @return {string} Product manufacturer
	 */
	public getManufacturer(): string {
		return this.product.companyName;
	}

	/**
	 * Returns product image url
	 * @return {string} Product image url
	 */
	public getImg(): string {
		return this.product.picture;
	}

	/**
	 * Sets device status to online
	 * @param {boolean} online Is device online?
	 */
	public setOnline(online: boolean): void {
		this.online = online;
	}

	/**
	 * Returns number of implemented binary outputs
	 * @return {number} Implemented binary outputs
	 */
	public getBinouts(): number {
		return this.binouts;
	}

	/**
	 * Sets number of implemented binary outputs
	 * @param {number} binouts Number of implemented binary outputs
	 */
	public setBinouts(binouts: number): void {
		this.binouts = binouts;
	}

	/**
	 * Sets number of implemented lights
	 * @param {boolean} light Number of implemented lights
	 */
	public setLight(light: boolean): void {
		this.light = light;
	}

	/**
	 * Sets implemented sensors
	 * @param {DbSensorData[]} sensors Implemented sensors
	 */
	public setSensors(sensors: DbSensorData[]): void {
		this.sensors = sensors;
	}

	/**
	 * Returns implemented sensors
	 * @return {DbSensorData[]} Implemented sensors
	 */
	public getSensors(): DbSensorData[] {
		return this.sensors;
	}

	/**
	 * Checks if device is online
	 * @return {boolean} Is device online?
	 */
	public isOnline(): boolean {
		return this.online;
	}

	/**
	 * Checks if device implements the Binary output standard
	 * @return {boolean} Does device implement binout?
	 */
	public hasBinout(): boolean {
		return this.binouts > 0;
	}

	/**
	 * Checks if device implements the light standard
	 * @return {boolean} Does device implement light?
	 */
	public hasLight(): boolean {
		return this.light;
	}

	/**
	 * Checks if device implements the sensor standard
	 * @return {boolean} Does device implement sensor?
	 */
	public hasSensor(): boolean {
		return this.sensors.length > 0;
	}

	/**
	 * Checks if device implements any standard
	 * @return {boolean} Does device implement any standard?
	 */
	public hasStandard(): boolean {
		return this.hasBinout() || this.hasLight() || this.hasSensor();
	}

	/**
	 * Returns device icon
	 * @return {string} Device MDI
	 */
	public getIcon(): string {
		if (this.address === 0) {
			return mdiHome;
		}
		if (this.discovered) {
			return mdiSignalCellularOutline;
		}
		return mdiCheck;
	}

	/**
	 * Returns device icon color
	 * @return {string} Device MDI color
	 */
	public getIconColor(): string {
		if (this.online) {
			return 'success';
		}
		return 'info';
	}

	/**
	 * Returns binout icon
	 * @return {string} Binout icon
	 */
	public getBinoutIcon(): string {
		if (this.hasBinout()) {
			return this.standardSupported;
		}
		return this.standardUnsupported;
	}

	/**
	 * Returns light icon
	 * @return {string} Light icon
	 */
	public getLightIcon(): string {
		if (this.hasLight()) {
			return this.standardSupported;
		}
		return this.standardUnsupported;
	}

	/**
	 * Returns sensor icon
	 * @return {string} Sensor icon
	 */
	public getSensorIcon(): string {
		if (this.hasSensor()) {
			return this.standardSupported;
		}
		return this.standardUnsupported;
	}
}

export default StandardDevice;
