import { computed, type ComputedRef } from 'vue';
import { useDisplay } from 'vuetify';

/**
 * Computes modal window width
 * @return {ComputedRef<string>} Computed modal window width
 */
export function getModalWidth(): ComputedRef<string> {
	return computed(() => {
		const display = useDisplay();
		if (display.lgAndUp.value) {
			return '50%';
		}
		if (display.md.value) {
			return '75%';
		}
		return '100%';
	});
}
