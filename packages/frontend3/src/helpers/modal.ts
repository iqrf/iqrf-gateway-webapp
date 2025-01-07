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
