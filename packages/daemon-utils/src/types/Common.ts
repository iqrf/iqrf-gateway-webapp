
import {
	type AuthorizeBondParams,
	type CoordinatorRestoreParams,
	type DiscoveryParams,
	type EmbedBondNodeParams,
	type EmbedSetDpaParams,
	type EmbedSetHopsParams,
} from './embed/Coordinator';
import { type PeripheralInformationParams } from './embed/Exploration';
import {
	type FrcSendParams,
	type FrcSendSelectiveParams,
	type EmbedFrcSetParamsRaw,
} from './embed/Frc';
import { type IoParams } from './embed/Io';
import { type NodeRestoreParams, type ValidateBondsParams } from './embed/Node';
import {
	type BatchParamsRaw,
	type EmbedTrConfigByteParams,
	type EmbedTrConfigParams,
	type IndicateParams,
	type LoadCodeParams,
	type SelectiveBatchParamsRaw,
	type SetSecurityParams,
	type SleepParams,
	type TestRfSignalParams,
} from './embed/Os';
import { type UartOpenParams } from './embed/Uart';
import { type IqmeshBackupParams, type IqmeshRestoreParams } from './iqmesh/Backup';
import {
	type IqmeshAutonetworkParams,
	type IqmeshBondNodeParams,
	type IqmeshRemoveBondCoordinatorParams,
	type IqmeshRemoveBondParams,
	type IqmeshSmartConnectParams,
} from './iqmesh/Bonding';
import { type IqmeshDpaHopsParams, type IqmeshDpaValueParams, type IqmeshFrcParams } from './iqmesh/DpaParameters';
import { type IqmeshEnumerateParams } from './iqmesh/Enumeration';
import { type IqmeshFrcResponseTimeParams, type IqmeshTestRfSignalParams } from './iqmesh/Maintenance';
import { type IqmeshOtaUploadParams } from './iqmesh/OtaUpload';
import { type IqmeshReadTrConfigParams, type IqmeshWriteTrConfigParams } from './iqmesh/TrConfiguration';
import { type SetOutputParams } from './standard/BinaryOutput';
import { type SendDaliCommandsParams, type SendDaliFrcCommandParams } from './standard/Dali';
import { type LightPowerParams } from './standard/Light';
import { type ReadSensorsFrcParams, type ReadSensorsParams } from './standard/Sensor';

/**
 * Generic embedded peripheral request parameters interface
 */
export interface EmbedSharedParams {
	/// Device address
	addr: number;
	/// HWPID filter
	hwpid?: number;
	/// Versbose response
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
