import {cilHome, cilX, cilCheckAlt, cilSignalCellular4} from '@coreui/icons';

/**
 * Class representing a device used in Network Manager.
 */
class Device {

	/**
	 * Device address
	 */
	public address: number;

	/**
	 * Is coordinator?
	 */
	public coordinator: boolean;

	/**
	 * Is bonded?
	 */
	public bonded: boolean;

	/**
	 * Is discovered
	 */
	public discovered: boolean;

	/**
	 * Is online?
	 */
	public online: boolean;

	/**
	 * Constructor
	 * @param address Device address
	 * @param coordinator Specifies if the device is a coordinator device
	 * @param bonded Specifies if the device is bonded
	 * @param discovered Specifies if the device is discovered
	 * @param online Specifies if the device is online
	 */
	constructor(address: number, coordinator: boolean, bonded: boolean = false, discovered :boolean = false, online :boolean = false) {
		this.address = address;
		this.coordinator = coordinator;
		this.bonded = bonded;
		this.discovered = discovered;
		this.online = online;
	}

	/**
	 * Returns the icon
	 * @returns Icon to render
	 */
	getIcon(): string[] {
		if (this.address === 0) {
			return cilHome;
		}
		if (this.bonded) {
			if (this.discovered) {
				return cilSignalCellular4;
			}
			return cilCheckAlt;
		}
		return cilX;
	}

	/**
	 * Returns the icon color
	 * @returns Icon color
	 */
	getIconColor(): string {
		if (this.address === 0) {
			return 'text-info';
		}
		if (this.bonded) {
			if (this.online) {
				return 'text-success';
			}
			return 'text-info';
		}
		return 'text-danger';
	}

	hasLink(): boolean {
		return this.coordinator || this.bonded;
	}
}

export default Device;
