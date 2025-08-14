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
	constructor(address: number, coordinator: boolean, bonded = false, discovered = false, online = false) {
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
