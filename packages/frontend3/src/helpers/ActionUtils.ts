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
	mdiArrowLeftThick,
	mdiArrowRightThick,
	mdiCancel,
	mdiCheckboxMarkedOutline,
	mdiCheckCircleOutline,
	mdiContentSave,
	mdiDelete,
	mdiDownload,
	mdiExport,
	mdiHelp,
	mdiImport,
	mdiInformationBox,
	mdiPencil,
	mdiPlay,
	mdiPlayCircleOutline,
	mdiPlus,
	mdiReload,
	mdiRestart,
	mdiRestore,
	mdiSkipNext,
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
			case Action.Confirm:
			case Action.Enable:
			case Action.Start:
				return 'green';
			case Action.Cancel:
			case Action.Close:
			case Action.Previous:
			case Action.Reset:
			case Action.Skip:
				return 'grey-darken-2';
			case Action.Disable:
			case Action.Delete:
			case Action.Stop:
				return 'red';
			case Action.Edit:
			case Action.Export:
				return 'info';
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
				return mdiCancel;
			case Action.Close:
				return mdiWindowClose;
			case Action.Confirm:
				return mdiCheckCircleOutline;
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
			case Action.Next:
				return mdiArrowRightThick;
			case Action.Previous:
				return mdiArrowLeftThick;
			case Action.Reload:
				return mdiReload;
			case Action.Reset:
				return mdiRestore;
			case Action.Restart:
				return mdiRestart;
			case Action.Save:
				return mdiContentSave;
			case Action.ShowDetails:
				return mdiInformationBox;
			case Action.Skip:
				return mdiSkipNext;
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
				return i18n.global.t('$iqrf.common.actions.add');
			case Action.Apply:
				return i18n.global.t('$iqrf.common.actions.apply');
			case Action.Cancel:
				return i18n.global.t('$iqrf.common.actions.cancel');
			case Action.Close:
				return i18n.global.t('$iqrf.common.actions.close');
			case Action.Confirm:
				return i18n.global.t('$iqrf.common.actions.confirm');
			case Action.Delete:
				return i18n.global.t('$iqrf.common.actions.delete');
			case Action.Disable:
				return i18n.global.t('$iqrf.common.actions.disable');
			case Action.Download:
				return i18n.global.t('$iqrf.common.actions.download');
			case Action.Enable:
				return i18n.global.t('$iqrf.common.actions.enable');
			case Action.Edit:
				return i18n.global.t('$iqrf.common.actions.edit');
			case Action.Export:
				return i18n.global.t('$iqrf.common.actions.export');
			case Action.Import:
				return i18n.global.t('$iqrf.common.actions.import');
			case Action.Next:
				return i18n.global.t('$iqrf.common.actions.next');
			case Action.Previous:
				return i18n.global.t('$iqrf.common.actions.previous');
			case Action.Reload:
				return i18n.global.t('$iqrf.common.actions.reload');
			case Action.Reset:
				return i18n.global.t('$iqrf.common.actions.reset');
			case Action.Restart:
				return i18n.global.t('$iqrf.common.actions.restart');
			case Action.Save:
				return i18n.global.t('$iqrf.common.actions.save');
			case Action.ShowDetails:
				return i18n.global.t('$iqrf.common.actions.showDetails');
			case Action.Skip:
				return i18n.global.t('$iqrf.common.actions.skip');
			case Action.Start:
				return i18n.global.t('$iqrf.common.actions.start');
			case Action.Stop:
				return i18n.global.t('$iqrf.common.actions.stop');
			case Action.Upload:
				return i18n.global.t('$iqrf.common.actions.upload');
			default:
				return '';
		}
	}

}
