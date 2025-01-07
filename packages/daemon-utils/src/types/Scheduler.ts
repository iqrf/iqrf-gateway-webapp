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

import { type DaemonApiRequest } from './Messages';
import { type MessagingInstance } from './Messaging';

/**
 * Scheduler record time specification interface
 */
export interface SchedulerRecordTimeSpec {
	/// Cron job time string
	cronTime: string;
	/// One-shot task
	exactTime: boolean;
	/// Periodic task period
	period: number;
	/// Periodic task
	periodic: boolean;
	/// One-shot job start time
	startTime: string;
}

/**
 * Scheduler record task interface
 */
export interface SchedulerRecordTask {
	/// Request to send
	message: DaemonApiRequest;
	/// Communication profiles
	messaging: MessagingInstance[] | string[];
}

/**
 * Scheduler record interface
 */
export interface SchedulerRecord {
	/// Active
	active?: boolean;
	/// Client ID
	clientId: string;
	/// Description
	description: string;
	/// Enabled
	enabled: boolean;
	/// New task ID
	newTaskId?: string;
	/// Persistent record
	persist: boolean;
	/// Task jobs
	task: SchedulerRecordTask[];
	/// Task ID
	taskId: string;
	/// Time specification
	timeSpec: SchedulerRecordTimeSpec;
}

/**
 * Scheduler record execution time condition enum
 */
export enum SchedulerTaskType {
	/// cron expression
	CRON = 'cron',
	/// datetime iso8601 string
	ONESHOT = 'oneshot',
	/// seconds
	PERIODIC = 'periodic',
}
