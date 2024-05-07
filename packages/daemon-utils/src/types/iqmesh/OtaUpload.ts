/**
 * IQMESH OTA upload parameters interface
 */
export interface IqmeshOtaUploadParams {
	/// Device address
	deviceAddr: number;
	/// Path to hex or iqrf file
	fileName: string;
	/// HWPID filter
	hwpId?: number;
	/// Loading action
	loadingAction: string;
	/// Memory address to store data at
	startMemAddr: number;
	/// Upload external eeprom data from hex file
	uploadEeepromData?: boolean;
	/// Upload internal eeprom data from hex file
	uploadEepromData?: boolean;
}
