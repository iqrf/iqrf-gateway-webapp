import { toString as cronToString } from 'cronstrue';

export class SchedulerCron {

	/**
	 * @property cronTraits Cron expression traits
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
			minValue: 1_970,
			maxValue: 2_099,
		},
	};

	/**
	 * @property {Map<string, string>} aliases Supported general cron expression aliases
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
	 * @property {string[]} dayAliases Day of week field aliases
	 */
	private static dayAliases: string[] = [
		'sun',
		'mon',
		'tue',
		'wed',
		'thu',
		'fri',
		'sat',
	];

	/**
	 * @property {string[]} monthAliases Months fields aliases
	 */
	private static monthAliases: string[] = [
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
	 * @return {string | undefined} Resolved CRON expression alias string, undefined if alias is not supported
	 */
	public static resolveExpressionAlias(alias: string): string|undefined {
		return this.aliases.get(alias);
	}

	/**
	 * Converts cron expression to human-readable string
	 * @param {string} expression CRON expression
	 * @return {string} Human-readable cron expression
	 */
	public static toHumanString(expression: string): string {
		const expr = expression.trim().split(' ');
		if (expr.length === 1) {
			const alias = SchedulerCron.resolveExpressionAlias(expr[0]);
			if (alias === undefined) {
				return '';
			}
			return cronToString(alias);
		} else if (expr.length >= 5 && expr.length <= 7) {
			return cronToString(expression);
		} else {
			return '';
		}
	}

	/**
	 * Convert cron expression to Daemon scheduler suitable format
	 * @param {string} expression CRON expression
	 * @return {string} Daemon API cron expression
	 */
	public static convertCron(expression: string): string {
		let cron = expression.replace('?', '*').split(' ');
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
		return cron.join(' ');
	}

}
