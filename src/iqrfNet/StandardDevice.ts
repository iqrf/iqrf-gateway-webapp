import {cilCheckAlt, cilHome, cilSignalCellular4} from '@coreui/icons';
import {IInfoSensorDetail} from '../interfaces/iqrfInfo';
import {IProduct} from '../interfaces/repository';
import i18n from '../i18n';

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
	private online = false

	/**
	 * Indicates that device implements the DALI standard
	 */
	private dali = false

	/**
	 * Array of implemented standard sensors
	 */
	private sensors: Array<IInfoSensorDetail> = []

	/**
	 * Array of implemented binary outputs
	 */
	private binouts = 0

	/**
	 * Array of implemented lights
	 */
	private lights = 0

	/**
	 * Show device details
	 */
	public showDetails = false

	/**
	 * Product details
	 */
	private product: IProduct

	/**
	 * Constructor
	 * @param address Device address
	 * @param mid Device MID
	 * @param hwpid Device HWPID
	 * @param hwpidVer Device HWPID version
	 * @param DPA Device DPA version
	 * @param OS Device OS build
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
		this.product = {
			name: 'Unknown',
			hwpid: this.hwpid,
			manufacturerID: -1,
			companyName: 'Unknown',
			homePage: '',
			picture: '',
			rfMode: -1,
			pictureOriginal: ''
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
		let dpa = this.dpa.toString(16).padStart(4, '0');
		if (dpa.startsWith('0')) {
			dpa = dpa[1] + '.' + dpa.substr(2, 2);
		} else {
			dpa = dpa.substr(0, 2) + '.' + dpa.substr(2, 2);
		}
		return dpa;
	}

	/**
	 * Returns device OS build
	 * @returns Device OS build
	 */
	getOs(): string {
		return this.os.toString(16).padStart(4, '0').toUpperCase();
	}

	/**
	 * Sets product information
	 * @param {IProduct|undefined} product Product information
	 */
	setProduct(product: IProduct|undefined): void {
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
	 * Returns number of implemented binouts
	 * @returns Implemented binouts
	 */
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

	/**
	 * Returns number of implemented lights
	 * @returns Implemented lights
	 */
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

	/**
	 * Returns breakdown of implemented sensors
	 * @returns Implemented sensors details
	 */
	getSensorDetails(): string {
		if (this.sensors.length > 0) {
			let message = '';
			this.sensors.forEach((sensor: IInfoSensorDetail) => {
				message += '\n\n' + i18n.t(
					'iqrfnet.standard.table.messages.sensor.detail',
					{
						name: sensor.name,
						short: sensor.shortName,
						type: sensor.type,
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
