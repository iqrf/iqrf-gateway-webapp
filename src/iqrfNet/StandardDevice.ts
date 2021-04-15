import {cilCheckAlt, cilHome, cilSignalCellular4} from '@coreui/icons';
import {IInfoSensorDetail} from '../interfaces/iqrfInfo';
import i18n from '../i18n';
import { IProduct } from '../interfaces/repository';

/**
 * Standard device object
 */
class StandardDevice {
	/**
	 * Device address
	 */
	private address: number

	/**
	 * Device module ID
	 */
	private mid: number

	/**
	 * Device hwpid
	 */
	private hwpid: number

	/**
	 * Device hwpid version
	 */
	private hwpidVer: number

	/**
	 * Device DPA version
	 */
	private dpa: number

	/**
	 * Device OS build
	 */
	private os: number

	/**
	 * Is device discovered?
	 */
	private discovered: boolean

	/**
	 * Is device online?
	 */
	private online: boolean

	/**
	 * Indicates that device implements the DALI standard
	 */
	private dali: boolean

	/**
	 * Array of implemented standard sensors
	 */
	private sensors: Array<IInfoSensorDetail>

	/**
	 * Array of implemented binary outputs
	 */
	private binouts: number

	/**
	 * Array of implemented lights
	 */
	private lights: number

	/**
	 * Show device details
	 */
	public showDetails = false

	/**
	 * Product name
	 */
	private productName: string

	/**
	 * Company name
	 */
	private company: string

	/**
	 * Product image
	 */
	private img: string

	/**
	 * Product url
	 */
	private url: string

	/**
	 * Constructor
	 * @param address Device address 
	 * @param hwpid Device HWPID
	 * @param mid Device MID
	 * @param discovered Is device discovered?
	 */
	constructor(address: number, mid: number, hwpid: number, hwpidVer: number, dpa: number, os: number, discovered = false) {
		this.address = address;
		this.mid = mid;
		this.hwpid = hwpid;
		this.hwpidVer = hwpidVer;
		this.dpa = dpa;
		this.os = os;
		this.discovered = discovered;
		this.online = false;
		this.dali = false;
		this.sensors = [];
		this.binouts = 0;
		this.lights = 0;
		this.productName = 'unknown';
		this.company = 'unknown';
		this.img = '';
		this.url = '';
	}

	getAddress(): number {
		return this.address;
	}

	getDpa(): string {
		let dpa = this.dpa.toString(16).padStart(4, '0');
		if (dpa.startsWith('0')) {
			dpa = dpa[1] + '.' + dpa.substr(2, 2);
		} else {
			dpa = dpa.substr(0, 2) + '.' + dpa.substr(2, 2);
		}
		return dpa;
	}

	getOs(): string {
		return this.os.toString(16).padStart(4, '0').toUpperCase();
	}

	getProductName(): string {
		return this.productName;
	}

	getManufacturer(): string {
		return this.company;
	}

	getImg(): string {
		return this.img;
	}

	getMid(): number {
		return this.mid;
	}

	getMidHex(): string {
		return this.mid.toString(16).toUpperCase();
	}

	getHwpid(): number {
		return this.hwpid;
	}

	getHwpidVer(): number {
		return this.hwpidVer;
	}

	getHwpidHex(): string {
		return this.hwpid.toString(16).toUpperCase();
	}

	/**
	 * Sets device status to online
	 * @param {boolean} online Is device online?
	 */
	setOnline(online: boolean): void {
		this.online = online;
	}

	getBinouts(): number {
		return this.binouts;
	}

	/**
	 * Sets number of implemented binouts
	 * @param {number} binouts Number of implemented binouts
	 */
	setBinouts(binouts: number): void {
		this.binouts = binouts;
	}

	/**
	 * Sets indication of dali implementation
	 * @param {boolean} dali Does device implement dali?
	 */
	setDali(dali: boolean): void {
		this.dali = dali;
	}

	getLights(): number {
		return this.lights;
	}

	/**
	 * Sets number of implemented lights
	 * @param {number} lights Number of implemented lights
	 */
	setLights(lights: number): void {
		this.lights = lights;
	}

	/**
	 * Sets implemented sensors
	 * @param {Array<IInfoSensorDetail>} sensors Implemented sensors
	 */
	setSensors(sensors: Array<IInfoSensorDetail>): void {
		this.sensors = sensors;
	}

	/**
	 * Sets product information
	 * @param {IProduct} product Product information
	 */
	setProduct(product: IProduct): void {
		this.productName = product.name;
		this.company = product.companyName;
		this.img = product.picture;
		this.url = product.homePage;
	}

	/**
	 * Returns device icon
	 * @returns Icon to render
	 */
	getIcon(): Array<string> {
		if (this.address === 0) {
			return cilHome;
		}
		if (this.discovered) {
			return cilSignalCellular4;
		}
		return cilCheckAlt;
	}

	/**
	 * Returns the icon color
	 * @returns Icon color
	 */
	getIconColor(): string {
		if (this.address === 0) {
			return 'text-info';
		}
		if (this.online) {
			return 'text-success';
		}
		return 'text-info';
	}

	getBinoutDetails(): string {
		return i18n.t(
			'iqrfnet.standard.table.messages.binout.' + (this.binouts > 0 ? 'implemented' : 'notImplemented'),
			{binouts: this.binouts}
		).toString();
	}

	getDaliDetails(): string {
		return i18n.t('iqrfnet.standard.table.messages.dali.' + (this.dali ? 'implemented' : 'notImplemented')).toString();

	}

	getLightDetails(): string {
		return i18n.t(
			'iqrfnet.standard.table.messages.light.' + (this.lights > 0 ? 'implemented' : 'notImplemented'),
			{lights: this.lights}
		).toString();
	}

	getSensorDetails(): string {
		if (this.sensors.length > 0) {
			let message = '';
			this.sensors.forEach((sensor: IInfoSensorDetail) => {
				message += '\n\n' + i18n.t(
					'iqrfnet.standard.table.messages.sensor.detail',
					{
						name: sensor.name,
						short: sensor.shortName,
						unit: sensor.unit === '?' ? 'N/A' : sensor.unit,
						commands: sensor.frcs.join(', '),
					},
				).toString();
			});
			return message.trim();
		} else {
			return i18n.t('iqrfnet.standard.table.messages.sensor.notImplemented').toString();
		}
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
	 * Checks if device implements the DALI standard
	 * @returns Does device implement dali?
	 */
	hasDali(): boolean {
		return this.dali;
	}

	/**
	 * Checks if device implements the light standard
	 * @returns Does device implement light?
	 */
	hasLight(): boolean {
		return this.lights > 0;
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
		return (this.hasBinout() || this.hasDali() || this.hasLight() || this.hasSensor());
	}
}

export default StandardDevice;
