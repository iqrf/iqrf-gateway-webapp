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
}
