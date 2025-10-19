/**
 * Wait until condition is true
 * @param {CallableFunction} condition Condition to wait for
 */
export function waitUntil(condition: () => boolean) {

	const resolver = (resolve: (value?: unknown) => void) => {
		if (condition()) {
			resolve();
		} else {
			window.setTimeout(() => {
				resolver(resolve);
			}, 300);
		}
	};

	return new Promise(resolver);
}
