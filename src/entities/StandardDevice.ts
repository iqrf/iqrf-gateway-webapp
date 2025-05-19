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
import { Product } from '@iqrf/iqrf-repository-client/types';
import {
	IqrfDbDeviceFull,
	IqrfDbSensorDetails,
} from '@/interfaces/DaemonApi/IqrfDb';
import i18n from '@/plugins/i18n';
import { cilCheckAlt, cilCheckCircle, cilHome, cilSignalCellular4, cilXCircle } from '@coreui/icons';

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
	 * Device virtual routing number
	 */
	private vrn: number;

	/**
	 * Device zone
	 */
	private zone: number;

	/**
	 * Parent device
	 */
	private parent: number | null;

	/**
	 * Is device online?
	 */
	private online = false;

	/**
	 * Array of implemented standard sensors
	 */
	private sensors: Array<IqrfDbSensorDetails> = [];

	/**
	 * Array of implemented binary outputs
	 */
	private binouts = 0;

	/**
	 * Device implements light
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
	private readonly standardSupported = cilCheckCircle;

	/**
	 * Standard unsupported icon
	 */
	private readonly standardUnsupported = cilXCircle;

	/**
	 * Constructor
	 * @param {IqrfDbDeviceFull} device Device object from Daemon API
	 */
	constructor(device: IqrfDbDeviceFull) {
		this.address = device.address;
		this.mid = device.mid;
		this.hwpid = device.hwpid;
		this.hwpidVer = device.hwpidVersion;
		this.osBuild = device.osBuild;
		this.osVersion = device.osVersion;
		this.dpa = device.dpa;
		this.discovered = device.discovered;
		this.vrn = device.vrn;
		this.zone = device.zone;
		this.parent = device.parent;
		this.product = {
			name: 'Unknown',
			hwpid: this.hwpid,
			manufacturerId: -1,
			companyName: 'Unknown',
			homePage: '',
			picture: '',
			rfModes: {
				STD: false,
				LP: false,
			},
			metadata: null,
		};
	}

	/**
	 * Returns device address
	 * @returns Device address
	 */
	getAddress(): number {
		return this.address;
	}

	/**
	 * Returns device MID
	 * @returns Device MID
	 */
	getMid(): number {
		return this.mid;
	}

	/**
	 * Returns device mid as hex string
	 * @returns Device MID hex
	 */
	getMidHex(): string {
		return this.mid.toString(16).toUpperCase();
	}

	/**
	 * Returns device HWPID
	 * @returns Device HWPID
	 */
	getHwpid(): number {
		return this.hwpid;
	}

	/**
	 * Returns device HWPID as hex string
	 * @returns Device HWPID hex
	 */
	getHwpidHex(): string {
		return this.hwpid.toString(16).toUpperCase();
	}

	/**
	 * Returns device HWPID version
	 * @returns Device HWPID version
	 */
	getHwpidVer(): number {
		return this.hwpidVer;
	}

	/**
	 * Returns formatted device DPA version
	 * @returns Device DPA version
	 */
	getDpa(): string {
		const major = (this.dpa >> 8).toString(16);
		const minor = (this.dpa & 0xff).toString(16).padStart(2, '0');
		return `${major}.${minor}`;
	}

	/**
	 * Returns device OS build
	 * @returns Device OS build
	 */
	getOsBuild(): string {
		return this.osBuild.toString(16).padStart(4, '0').toUpperCase();
	}

	/**
	 * Sets IQRF OS version
	 * @param version IQRF OS version
	 */
	setOsVersion(version: string): void {
		this.osVersion = version;
	}

	/**
	 * Returns device OS
	 * @returns Device OS
	 */
	getOs(): string {
		const build = this.getOsBuild();
		if (!this.osVersion || this.osVersion === '') {
			return `${i18n.t('forms.unknown').toString()} (${build})`;
		}
		return `${this.osVersion} (${build})`;
	}

	/**
	 * Sets product information
	 * @param {Product|undefined} product Product information
	 */
	setProduct(product: Product|undefined): void {
		if (product === undefined) {
			return;
		}
		this.product = product;
	}

	/**
	 * Returns product name
	 * @returns Product name
	 */
	getProductName(): string {
		return this.product.name;
	}

	/**
	 * Returns product manufacturer
	 * @returns Product manufacturer
	 */
	getManufacturer(): string {
		return this.product.companyName;
	}

	/**
	 * Returns product image url
	 * @returns Product image url
	 */
	getImg(): string {
		return this.product.picture;
	}

	/**
	 * Sets device status to online
	 * @param {boolean} online Is device online?
	 */
	setOnline(online: boolean): void {
		this.online = online;
	}

	/**
	 * Returns number of implemented binary outputs
	 * @returns Implemented binary outputs
	 */
	getBinouts(): number {
		return this.binouts;
	}

	/**
	 * Sets number of implemented binary outputs
	 * @param {number} binouts Number of implemented binary outputs
	 */
	setBinouts(binouts: number): void {
		this.binouts = binouts;
	}

	/**
	 * Sets Light implemented
	 * @param {boolean} light Light implemented
	 */
	setLight(light: boolean): void {
		this.light = light;
	}

	/**
	 * Sets implemented sensors
	 * @param {IqrfDbSensorDetails[]} sensors Implemented sensors
	 */
	setSensors(sensors: IqrfDbSensorDetails[]): void {
		this.sensors = sensors;
	}

	/**
	 * Returns implemented sensors
	 * @returns Implemented sensors
	 */
	getSensors(): IqrfDbSensorDetails[] {
		return this.sensors;
	}

	/**
	 * Checks if device is online
	 * @returns Is device online?
	 */
	isOnline(): boolean {
		return this.online;
	}

	/**
	 * Checks if device implements the Binary output standard
	 * @returns Does device implement binout?
	 */
	hasBinout(): boolean {
		return this.binouts > 0;
	}

	/**
	 * Checks if device implements the light standard
	 * @returns Does device implement light?
	 */
	hasLight(): boolean {
		return this.light;
	}

	/**
	 * Checks if device implements the sensor standard
	 * @returns Does device implement sensor?
	 */
	hasSensor(): boolean {
		return this.sensors.length > 0;
	}

	/**
	 * Checks if device implements any standard
	 * @returns Does device implement any standard?
	 */
	hasStandard(): boolean {
		return (this.hasBinout() || this.hasLight() || this.hasSensor());
	}

	/**
	 * Returns device icon
	 * @returns Device MDI
	 */
	getIcon(): string[] {
		if (this.address === 0) {
			return cilHome;
		}
		if (this.discovered) {
			return cilSignalCellular4;
		}
		return cilCheckAlt;
	}

	/**
	 * Returns device icon color
	 * @return Device MDI color
	 */
	getIconColor(): string {
		if (this.online) {
			return 'text-success';
		}
		return 'text-info';
	}

	/**
	 * Returns binout icon
	 * @returns Binout icon
	 */
	getBinoutIcon(): string[] {
		if (this.hasBinout()) {
			return this.standardSupported;
		}
		return this.standardUnsupported;
	}

	/**
	 * Returns light icon
	 * @returns Light icon
	 */
	getLightIcon(): string[] {
		if (this.hasLight()) {
			return this.standardSupported;
		}
		return this.standardUnsupported;
	}

	/**
	 * Returns sensor icon
	 * @returns Sensor icon
	 */
	getSensorIcon(): string[] {
		if (this.hasSensor()) {
			return this.standardSupported;
		}
		return this.standardUnsupported;
	}
}

export default StandardDevice;
