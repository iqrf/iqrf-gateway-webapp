/**
 * Copyright 2017-2023 IQRF Tech s.r.o.
 * Copyright 2019-2023 MICRORISC s.r.o.
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
import cronstrue from 'cronstrue';

import {TimeSpecTypes} from '@/enums/Config/Scheduler';
import {ISchedulerRecord, ISchedulerRecordTask, ISchedulerRecordTimeSpec} from '@/interfaces/DaemonApi/Scheduler';

/**
 * Scheduler record auxiliary class
 */
export default class SchedulerRecord {

	/**
	 * @constant cronTraits Cron expression traits
	 */
	public static cronTraits = {
		presetId: 'schedulerCron',
		useSeconds: true,
		useYears: true,
		useAliases: true,
		useBlankDay: true,
		allowOnlyOneBlankDayField: false,
		seconds: {
			minValue: 0,
			maxValue: 59,
		},
		minutes: {
			minValue: 0,
			maxValue: 59,
		},
		hours: {
			minValue: 0,
			maxValue: 23,
		},
		daysOfMonth: {
			minValue: 1,
			maxValue: 31,
		},
		months: {
			minValue: 1,
			maxValue: 12,
		},
		daysOfWeek: {
			minValue: 0,
			maxValue: 6,
		},
		years: {
			minValue: 1970,
			maxValue: 2099,
		},
	};

	/**
	 * @constant {Map<string, string>} aliases Supported general cron expression aliases
	 */
	private static aliases: Map<string, string> = new Map<string, string>([
		['@yearly', '0 0 0 1 1 * *'],
		['@annually', '0 0 0 1 1 * *'],
		['@monthly', '0 0 0 1 * * *'],
		['@weekly', '0 0 0 * * 0 *'],
		['@daily', '0 0 0 * * * *'],
		['@hourly', '0 0 * * * * *'],
		['@minutely', '0 * * * * * *'],
	]);

	/**
	 * @constant {Array<string>} dayAliases Day of week field aliases
	 */
	private static dayAliases: Array<string> = [
		'sun',
		'mon',
		'tue',
		'wed',
		'thu',
		'fri',
		'sat',
	];

	/**
	 * @constant {Array<string>} monthAliases Months fields aliases
	 */
	private static monthAliases: Array<string> = [
		'jan',
		'feb',
		'mar',
		'apr',
		'may',
		'jun',
		'jul',
		'aug',
		'sep',
		'oct',
		'nov',
		'dec',
	];

	/**
	 * Resolves cron expression alias
	 * @param {string} alias CRON expression alias
	 * @returns Resolved CRON expression alias string
	 * @returns Undefined if alias is not supported
	 */
	static resolveExpressionAlias(alias: string): string|undefined {
		return this.aliases.get(alias);
	}

	/**
	 * Converts cron expression to human-readable string
	 * @param {string} expression CRON expression
	 * @returns Human-readable cron expression
	 */
	static expressionToString(expression: string): string {
		const expr = expression.trim().split(' ');
		if (expr.length === 1) {
			const alias = SchedulerRecord.resolveExpressionAlias(expr[0]);
			return (alias === undefined ? '' : cronstrue.toString(alias));
		} else if (expr.length >= 5 && expr.length <= 7) {
			return cronstrue.toString((expression));
		} else {
			return '';
		}
	}

	/**
	 * Converts scheduler record to API request-ready format
	 * @param {TimeSpecTypes} type Time specification execution condition type
	 * @param {ISchedulerRecord} record Scheduler record
	 * @param {boolean} newTask Indicates that the record is a new task
	 * @returns Converted scheduler record
	 */
	static prepareRecord(type: TimeSpecTypes, record: ISchedulerRecord, newTask = false): ISchedulerRecord {
		if (newTask) {
			delete record.newTaskId;
		}
		const tasks: Array<ISchedulerRecordTask> = [];
		record.task.forEach((task: ISchedulerRecordTask) => {
			const message = typeof task.message === 'string' ? JSON.parse(task.message) : task.message;
			const messaging = task.messaging;
			tasks.push({
				message: message,
				messaging: messaging,
			});
		});
		record.task = tasks;
		record.timeSpec = SchedulerRecord.prepareTimeSpec(type, record.timeSpec);
		delete record.active;
		return record;
	}

	/**
	 * Converts scheduler record time specification to API request-ready format
	 * @param {TimeSpecTypes} type Time specification execution condition type
	 * @param {ISchedulerRecordTimeSpec} timeSpec Time specification
	 * @returns Converted time specification
	 */
	static prepareTimeSpec(type: TimeSpecTypes, timeSpec: ISchedulerRecordTimeSpec): ISchedulerRecordTimeSpec {
		const period = timeSpec.period;
		const startTime = timeSpec.startTime;
		let cron = (timeSpec.cronTime as string).replace('?', '*').split(' ');
		timeSpec.periodic = timeSpec.exactTime = false;
		timeSpec.startTime = '';
		timeSpec.period = 0;
		timeSpec.cronTime = '';
		if (type === TimeSpecTypes.EXACT) {
			timeSpec.startTime = startTime;
			timeSpec.exactTime = true;
		} else if (type === TimeSpecTypes.PERIODIC) {
			timeSpec.period = period;
			timeSpec.periodic = true;
		} else {
			if (cron.length === 1) {
				const alias = this.aliases.get(cron[0]);
				if (alias !== undefined) {
					cron = alias.split(' ');
				}
			}
			if (this.dayAliases.includes(cron[5])) {
				cron[5] = this.dayAliases.indexOf(cron[5]).toString();
			}
			if (this.monthAliases.includes(cron[4])) {
				cron[4] = (this.monthAliases.indexOf(cron[4]) + 1).toString();
			}
			timeSpec.cronTime = cron.join(' ');
		}
		return timeSpec;
	}
}
