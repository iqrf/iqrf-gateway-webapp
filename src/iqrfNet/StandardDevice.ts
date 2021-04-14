import {cilCheckAlt, cilHome, cilSignalCellular4} from '@coreui/icons';
import {IInfoSensorDetail} from '../interfaces/iqrfInfo';
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
	 * Product name
	 */
	private productName: string

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
	}

	getAddress(): number {
		return this.address;
	}

	getProductName(): string {
		return this.productName;
	}

	getMid(): number {
		return this.mid;
	}

	getHwpid(): number {
		return this.hwpid;
	}

	isOnline(): boolean {
		return this.online;
	}

	hasBinout(): boolean {
		return this.binouts > 0;
	}

	hasDali(): boolean {
		return this.dali;
	}

	hasLight(): boolean {
		return this.lights > 0;
	}

	hasSensor(): boolean {
		return this.sensors.length > 0;
	}

	hasStandard(): boolean {
		return (this.hasBinout() || this.hasDali() || this.hasLight() || this.hasSensor());
	}

	/**
	 * Sets device status to online
	 * @param {boolean} online Is device online?
	 */
	setOnline(online: boolean): void {
		this.online = online;
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

	setProductName(name: string): void {
		this.productName = name;
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

	getGeneralDetails(): string {
		let dpa = this.dpa.toString(16).padStart(4, '0');
		if (dpa.startsWith('0')) {
			dpa = dpa[1] + '.' + dpa.substr(2, 2);
		} else {
			dpa = dpa.substr(0, 2) + '.' + dpa.substr(2, 2);
		}
		return i18n.t(
			'iqrfnet.standard.grid.messages.device.details',
			{
				mid: this.mid,
				hwpid: this.hwpid,
				hwpidVer: this.hwpidVer,
				os: this.os.toString(16).padStart(4, '0').toUpperCase(),
				dpa: dpa
			}
		).toString();
	}

	getBinoutDetails(): string {
		return i18n.t(
			'iqrfnet.standard.grid.messages.binout.' + (this.binouts > 0 ? 'implemented' : 'notImplemented'),
			{binouts: this.binouts}
		).toString();
	}

	getDaliDetails(): string {
		return i18n.t('iqrfnet.standard.grid.messages.dali.' + (this.dali ? 'implemented' : 'notImplemented')).toString();
	}

	getLightDetails(): string {
		return i18n.t(
			'iqrfnet.standard.grid.messages.light.' + (this.lights > 0 ? 'implemented' : 'notImplemented'),
			{lights: this.lights}
		).toString();
	}

	getSensorDetails(): string {
		if (this.sensors.length > 0) {
			let message = i18n.t('iqrfnet.standard.grid.messages.sensor.implemented').toString();
			this.sensors.forEach((sensor: IInfoSensorDetail) => {
				message += '\n\n' + i18n.t(
					'iqrfnet.standard.grid.messages.sensor.detail',
					{
						name: sensor.name,
						short: sensor.shortName,
						unit: sensor.unit === '?' ? 'N/A' : sensor.unit,
						commands: sensor.frcs.join(', '),
					},
				).toString();
			});
			return message;
		} else {
			return i18n.t('iqrfnet.standard.grid.messages.sensor.notImplemented').toString();
		}
	}

	/**
	 * Returns standard device details for tooltip
	 * @returns Device details string
	 */
	getDetails(): string {
		let message = '';
		message = i18n.t(
			'iqrfnet.standard.grid.messages.device.general',
			{hwpid: this.hwpid, mid: this.mid}
		).toString();
		message += '\n\n' + i18n.t('iqrfnet.standard.binaryOutput.title').toString();
		message += '\n' + this.getBinoutDetails();
		message += '\n\n' + i18n.t('iqrfnet.standard.dali.title').toString();
		message += '\n' + this.getDaliDetails();
		message += '\n\n' + i18n.t('iqrfnet.standard.light.title').toString();
		message += '\n' + this.getLightDetails();
		message += '\n\n' + i18n.t('iqrfnet.standard.sensor.title').toString();
		message += '\n' + this.getSensorDetails();
		return message;
	}
}

export default StandardDevice;
