import { IqmeshServiceMessages } from '../../enums';
import {
	type IqmeshBackupParams,
	type IqmeshRestoreParams,
	type IqmeshSharedParams,
} from '../../types';
import { type DaemonMessageOptions } from '../../utils';

import { BaseIqmeshService } from './BaseIqmeshService';

/**
 * IQMESH Backup API service
 */
export class BackupService extends BaseIqmeshService {

	/**
	 * Perform backup
	 * @param {IqmeshSharedParams} shared Shared IQMESH request parameters
	 * @param {IqmeshBackupParams} params IQMESH Backup request parameters
	 * @param {DaemonMessageOptions} options Message options
	 * @return {DaemonMessageOptions} Message options with request
	 */
	public static backup(shared: IqmeshSharedParams, params: IqmeshBackupParams, options: DaemonMessageOptions): DaemonMessageOptions {
		return this.buildOptionsWithRequest(
			IqmeshServiceMessages.Backup,
			shared,
			params,
			options,
		);
	}

	/**
	 * Perform restore
	 * @param {IqmeshSharedParams} shared Shared IQMESH request parameters
	 * @param {IqmeshRestoreParams} params IQMESH Restore request parameters
	 * @param {DaemonMessageOptions} options Message options
	 * @return {DaemonMessageOptions} Message options with request
	 */
	public static restore(shared: IqmeshSharedParams, params: IqmeshRestoreParams, options: DaemonMessageOptions): DaemonMessageOptions {
		return this.buildOptionsWithRequest(
			IqmeshServiceMessages.Restore,
			shared,
			params,
			options,
		);
	}
}
