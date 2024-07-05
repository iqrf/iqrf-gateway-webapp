import {
	type AuthorizeBondParams,
	type CoordinatorRestoreParams,
	type DiscoveryParams,
	type EmbedBondNodeParams,
	type EmbedSetDpaParams,
	type EmbedSetHopsParams,
	type PeripheralInformationParams ,
	type EmbedFrcSetParamsRaw,
	type FrcSendParams,
	type FrcSendSelectiveParams,
	type IoParams,
	type NodeRestoreParams,
	type ValidateBondsParams ,
	type BatchParamsRaw,
	type EmbedTrConfigByteParams,
	type EmbedTrConfigParams,
	type IndicateParams,
	type LoadCodeParams,
	type SelectiveBatchParamsRaw,
	type SetSecurityParams,
	type SleepParams,
	type TestRfSignalParams,
	type UartOpenParams,
} from './embed';
import {
	type IqmeshBackupParams,
	type IqmeshRestoreParams,
	type IqmeshAutonetworkParams,
	type IqmeshBondNodeParams,
	type IqmeshRemoveBondCoordinatorParams,
	type IqmeshRemoveBondParams,
	type IqmeshSmartConnectParams,
	type IqmeshDpaHopsParams,
	type IqmeshDpaValueParams,
	type IqmeshFrcParams,
	type IqmeshEnumerateParams,
	type IqmeshFrcResponseTimeParams,
	type IqmeshTestRfSignalParams,
	type IqmeshOtaUploadParams,
	type IqmeshReadTrConfigParams,
	type IqmeshWriteTrConfigParams,
} from './iqmesh';
import {
	type SetOutputParams,
	type SendDaliCommandsParams,
	type SendDaliFrcCommandParams,
	type LightPowerParams,
	type ReadSensorsFrcParams,
	type ReadSensorsParams,
} from './standard';

/**
 * Generic embedded peripheral request parameters interface
 */
export interface EmbedSharedParams {
	/// Device address
	addr: number;
	/// HWPID filter
	hwpid?: number;
	/// Verbose response
	returnVerbose?: boolean;
}

/**
 * Generic IQMESH service request parameters interface
 */
export interface IqmeshSharedParams {
	/// Number of DPA retry transactions
	repeat?: number;
	/// Return more verbose response
	returnVerbose?: boolean;
}

/**
 * IQMESH network services request parameters
 */
export interface IqmeshNetworkParams {
	/// HWPID filter
	hwpId: number;
}

/**
 * Address-MID pair interface
 */
export interface AddrMidPairParams {
	/// Device address
	bondAddr: number;
	/// Device MID
	mid: number;
}

/**
 * Embedded Coordinator and Node backup parameters interface
 */
export interface EmbedBackupParams {
	/// Index of data block to read from
	index: number;
}

/**
 * Embedded LEDR and LEDG parameters interface
 */
export interface LedSetParams {
	/// Set LED on or off
	onOff: boolean;
}

/**
 * Embedded EEPROM, EEEPROM and RAM read parameters interface
 */
export interface MemoryReadParams {
	/// Memory address to read from
	address: number;
	/// Number of bytes to read
	len: number;
}

/**
 * Embedded EEPROM, EEEPROM and RAM write parameters interface
 */
export interface MemoryWriteParams {
	/// Memory address to write to
	address: number;
	/// Data to write
	pData: number[];
}

/**
 * Embedded SPI and UART ReadWrite parameters interface
 */
export interface WriteReadParams {
	/// Read timeout in 10ms unit
	readTimeout: number;
	/// Data to write
	writtenData: number[];
}

/**
 * Union of embedded peripherals and standards API request parameters interfaces
 */
export type EmbedRequestParams =
	| AddrMidPairParams
	| AuthorizeBondParams
	| BatchParamsRaw
	| CoordinatorRestoreParams
	| DiscoveryParams
	| EmbedBackupParams
	| EmbedBondNodeParams
	| EmbedFrcSetParamsRaw
	| EmbedSetDpaParams
	| EmbedSetHopsParams
	| EmbedTrConfigParams
	| EmbedTrConfigByteParams
	| FrcSendParams
	| FrcSendSelectiveParams
	| IndicateParams
	| IoParams
	| LedSetParams
	| LightPowerParams
	| LoadCodeParams
	| MemoryReadParams
	| MemoryWriteParams
	| NodeRestoreParams
	| PeripheralInformationParams
	| ReadSensorsParams
	| ReadSensorsFrcParams
	| SelectiveBatchParamsRaw
	| SendDaliCommandsParams
	| SendDaliFrcCommandParams
	| SetOutputParams
	| SetSecurityParams
	| SleepParams
	| TestRfSignalParams
	| UartOpenParams
	| ValidateBondsParams
	| WriteReadParams

/**
 * Union of IQMESH service API request parameters interfaces
 */
export type IqmeshRequestParams =
	| IqmeshDpaHopsParams
	| IqmeshDpaValueParams
	| IqmeshAutonetworkParams
	| IqmeshBackupParams
	| IqmeshBondNodeParams
	| IqmeshEnumerateParams
	| IqmeshFrcParams
	| IqmeshFrcResponseTimeParams
	| IqmeshNetworkParams
	| IqmeshReadTrConfigParams
	| IqmeshRestoreParams
	| IqmeshSmartConnectParams
	| IqmeshTestRfSignalParams
	| IqmeshWriteTrConfigParams
	| IqmeshOtaUploadParams
	| IqmeshRemoveBondCoordinatorParams
	| IqmeshRemoveBondParams
