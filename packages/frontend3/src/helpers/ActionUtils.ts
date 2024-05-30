/**
 * Copyright 2017-2024 IQRF Tech s.r.o.
 * Copyright 2019-2024 MICRORISC s.r.o.
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

import {
	mdiCheckboxMarkedOutline,
	mdiDelete,
	mdiDownload,
	mdiExport,
	mdiHelp,
	mdiImport, mdiInformationBox,
	mdiPencil,
	mdiPlay,
	mdiPlayCircleOutline,
	mdiPlus,
	mdiReload,
	mdiRestart,
	mdiStop,
	mdiStopCircleOutline,
	mdiUpload,
	mdiWindowClose,
} from '@mdi/js';

import i18n from '@/plugins/i18n';
import { Action } from '@/types/Action';

/**
 * Action utility class
 */
export class ActionUtils {

	/**
	 * Returns color for the specified action
	 * @param {Action} action Action
	 * @return {string} Color for the specified action
	 */
	public static getColor(action: Action): string {
		switch (action) {
			case Action.Add:
			case Action.Apply:
			case Action.Enable:
			case Action.Start:
				return 'green';
			case Action.Cancel:
				return 'grey-darken-2';
			case Action.Disable:
			case Action.Delete:
			case Action.Stop:
				return 'red';
			case Action.Edit:
				return 'primary';
			default:
				return 'primary';
		}
	}

	/**
	 * Returns icon for the specified action
	 * @param {Action} action Action
	 * @return {string} Icon for the specified action
	 */
	public static getIcon(action: Action): string {
		switch (action) {
			case Action.Add:
				return mdiPlus;
			case Action.Apply:
				return mdiCheckboxMarkedOutline;
			case Action.Cancel:
				return mdiWindowClose;
			case Action.Delete:
				return mdiDelete;
			case Action.Disable:
				return mdiStopCircleOutline;
			case Action.Download:
				return mdiDownload;
			case Action.Edit:
				return mdiPencil;
			case Action.Enable:
				return mdiPlayCircleOutline;
			case Action.Export:
				return mdiExport;
			case Action.Import:
				return mdiImport;
			case Action.Reload:
				return mdiReload;
			case Action.Restart:
				return mdiRestart;
			case Action.ShowDetails:
				return mdiInformationBox;
			case Action.Start:
				return mdiPlay;
			case Action.Stop:
				return mdiStop;
			case Action.Upload:
				return mdiUpload;
			default:
				return mdiHelp;
		}
	}

	/**
	 * Returns text for the specified action
	 * @param {Action} action Action
	 * @return {string} Text for the specified action
	 */
	public static getText(action: Action): string {
		switch (action) {
			case Action.Add:
				return i18n.global.t('components.common.actions.add').toString();
			case Action.Apply:
				return i18n.global.t('components.common.actions.apply').toString();
			case Action.Cancel:
				return i18n.global.t('components.common.actions.cancel').toString();
			case Action.Delete:
				return i18n.global.t('components.common.actions.delete').toString();
			case Action.Disable:
				return i18n.global.t('components.common.actions.disable').toString();
			case Action.Download:
				return i18n.global.t('components.common.actions.download').toString();
			case Action.Enable:
				return i18n.global.t('components.common.actions.enable').toString();
			case Action.Edit:
				return i18n.global.t('components.common.actions.edit').toString();
			case Action.Export:
				return i18n.global.t('components.common.actions.export').toString();
			case Action.Import:
				return i18n.global.t('components.common.actions.import').toString();
			case Action.Reload:
				return i18n.global.t('components.common.actions.reload').toString();
			case Action.Restart:
				return i18n.global.t('components.common.actions.restart').toString();
			case Action.ShowDetails:
				return i18n.global.t('components.common.actions.showDetails').toString();
			case Action.Start:
				return i18n.global.t('components.common.actions.start').toString();
			case Action.Stop:
				return i18n.global.t('components.common.actions.stop').toString();
			case Action.Upload:
				return i18n.global.t('components.common.actions.upload').toString();
			default:
				return '';
		}
	}

}
