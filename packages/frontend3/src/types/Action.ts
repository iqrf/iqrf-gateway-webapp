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

/**
 * Card actions
 */
export enum Action {
	/// Add
	Add = 'add',
	/// Apply
	Apply = 'apply',
	/// Cancel
	Cancel = 'cancel',
	/// Close
	Close = 'close',
	/// Confirm
	Confirm = 'confirm',
	/// Custom
	Custom = 'custom',
	/// Delete
	Delete = 'delete',
	/// Disable
	Disable = 'disable',
	/// Download
	Download = 'download',
	/// Edit
	Edit = 'edit',
	/// Enable
	Enable = 'enable',
	/// Export
	Export = 'export',
	/// Import
	Import = 'import',
	/// Next
	Next = 'next',
	/// Previous
	Previous = 'previous',
	/// Reload
	Reload = 'reload',
	/// Reset
	Reset = 'reset',
	/// Restart
	Restart = 'restart',
	/// Save
	Save = 'save',
	/// Show details
	ShowDetails = 'show-details',
	/// Skip
	Skip = 'skip',
	/// Start
	Start = 'start',
	/// Stop
	Stop = 'stop',
	/// Upload
	Upload = 'upload',
}
