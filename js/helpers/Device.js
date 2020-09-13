import {cilHome, cilX, cilCheckAlt, cilSignalCellular4} from '@coreui/icons';

/**
 * Class representing a device used in Network Manager.
 */
class Device {
	/**
	 * Constructor
	 * @param addr Device address
	 * @param coordinator Specifies if the device is a coordinator device
	 * @param bonded Specifies if the device is bonded
	 * @param discovered Specifies if the device is discovered
	 * @param online Specifies if the device is online
	 */
	constructor(addr, coordinator, bonded = false, discovered = false, online = false) {
		this.addr = addr;
		this.coordinator = coordinator;
		this.bonded = bonded;
		this.discovered = discovered;
		this.online = online;
	}

	/**
	 * Returns the icon
	 * @returns {string[]} Icon to render
	 */
	getIcon() {
		if (this.addr === 0) {
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
	 * @returns {string} Icon color
	 */
	getIconColor() {
		if (this.addr === 0) {
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

	hasLink() {
		return this.coordinator || this.bonded;
	}
}

export default Device;
