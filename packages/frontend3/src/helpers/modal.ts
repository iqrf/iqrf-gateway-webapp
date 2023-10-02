
import {useDisplay} from 'vuetify';
import {computed, ComputedRef} from 'vue';

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
