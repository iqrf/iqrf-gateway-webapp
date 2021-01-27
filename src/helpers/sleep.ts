/**
 * Custom sleep function, pauses execution for specified number of milliseconds
 * @param ms Sleep duration in milliseconds
 */
export function sleep(ms: number): Promise<void> {
	return new Promise((resolve) => setTimeout(resolve, ms));
}
