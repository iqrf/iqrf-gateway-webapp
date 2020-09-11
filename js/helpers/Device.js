import {cilHome, cilX, cilCheckAlt, cilSignalCellular4} from '@coreui/icons';
/**
 * Class representing a device used in Network Manager.
 * @param addr Device address
 * @param coordinator Specifies if the device is a coordinator device
 * @param bonded Specifies if the device is bonded
 * @param discovered Specifies if the device is discovered
 * @param online Specifies if the device is online
 */
export default class Device {
	constructor(addr, coordinator, bonded = false, discovered = false, online = false) {
		this.addr = addr;
		this.coordinator = coordinator;
		this.bonded = bonded;
		this.discovered = discovered;
		this.online = online;
	}

	getIcon() {
		if (this.addr === 0) {
			return cilHome;
		} else {
			if (this.bonded) {
				if (this.discovered) {
					return cilSignalCellular4;
				} else {
					return cilCheckAlt;
				}
			} else {
				return cilX;
			}
		}
	}

	getIconColor() {
		if (this.addr === 0) {
			return 'text-info';
		} else {
			if (this.bonded) {
				if (this.online) {
					return 'text-success';
				} else {
					return 'text-info';
				}
			} else {
				return 'text-danger';
			}
		}
	}
}
