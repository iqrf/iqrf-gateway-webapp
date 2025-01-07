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

import { computed, onMounted, onUnmounted, ref } from 'vue';

export const useBreakpoints = () => {
	const windowWidth = ref(window.innerWidth);

	const onWidthChange = () => windowWidth.value = window.innerWidth;
	onMounted(() => window.addEventListener('resize', onWidthChange));
	onUnmounted(() => window.removeEventListener('resize', onWidthChange));

	const type = computed(() => {
		if (windowWidth.value < 550) {
			return 'xs';
		}
		if (windowWidth.value >= 550 && windowWidth.value < 1200) {
			return 'md';
		}
		return 'lg';
	});

	const width = computed(() => windowWidth.value);

	return { width, type };
};
