import { type AddrMidPairParams } from '../Common';

/**
 * Embedded Node restore parameters interface
 */
export interface NodeRestoreParams {
	/// Node backup data
	backupData: number[];
}

/**
 * Embedded Node validate bonds parameters interface
 */
export interface ValidateBondsParams {
	nodes: AddrMidPairParams[];
}
