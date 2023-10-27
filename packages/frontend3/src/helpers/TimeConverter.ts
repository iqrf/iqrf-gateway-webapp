import { DateTime } from 'luxon';

import { useLocaleStore } from '@/store/locale';

/**
 * Time conversion utility class
 */
export default class TimeConverter {

	static millisToDateTimeFull(num: number): string {
		const localeStore = useLocaleStore();
		return DateTime.fromMillis(num)
			.setLocale(localeStore.getLocale)
			.toLocaleString(DateTime.DATETIME_FULL_WITH_SECONDS);
	}

	static secondsToDuration(num: number): string {
		let s = num;
		const d = Math.floor(s / (3600 * 24));
		s -= d*3600*24;
		const h = Math.floor(s / 3600);
		s -= h*3600;
		const m = Math.floor(s / 60);
		s -= m*60;
		return `${d}d, ${h}h, ${m}m, ${s}s`;
	}

	static hoursToMillis(hours = 0, minutes = 0, seconds = 0): number {
		return (hours * 60 * 60 + minutes * 60 + seconds) * 1000;
	}
}
