/**
 * Copyright 2017-2021 IQRF Tech s.r.o.
 * Copyright 2019-2021 MICRORISC s.r.o.
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
 * Scheduler task time interface
 */
export interface ITaskTimeSpec {
	cronTime: Array<string>|string
	exactTime: boolean
	period: number
	periodic: boolean
	startTime: string
}

/**
 * Scheduler task dpa message interface
 */
export interface ITaskDpaMessage {
	mType: string
	data: Record<string, unknown>
}

/**
 * Scheduler task message interface
 */
export interface ITaskMessage {
	message: ITaskDpaMessage
	messaging: string
}

/**
 * Scheduler task interface
 */
export interface ITaskBase {
	clientId: string
	taskId: number
	timeSpec: ITaskTimeSpec
}

/**
 * Scheduler task from REST interface
 */
export interface ITaskRest extends ITaskBase {
	task: Array<ITaskMessage>
}

/**
 * Scheduler task from Daemon interface
 */
export interface ITaskDaemon extends ITaskBase {
	task: Array<ITaskMessage>|ITaskMessage
}

/**
 * Scheduler task messaging data for form interface
 */
export interface ITaskMessaging {
	message: string
	messaging: Array<string>
}
