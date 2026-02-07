/**
 * Copyright 2017-2026 IQRF Tech s.r.o.
 * Copyright 2019-2026 MICRORISC s.r.o.
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

import { createPinia, setActivePinia } from 'pinia';
import { beforeEach, describe, expect, test } from 'vitest';

import { useSidebarStore } from '@/store/sidebar';

describe('Sidebar store', (): void => {

	/**
	 * Sets up the test environment before each test
	 * Initializes a new Pinia instance and sets it as active
	 */
	beforeEach((): void => {
		setActivePinia(createPinia());
	});

	test('sets sidebar visibility', (): void => {
		expect.assertions(4);
		const store = useSidebarStore();
		expect(store.visible).toBe(true);
		expect(store.isVisible).toBe(true);
		store.setVisibility(false);
		expect(store.visible).toBe(false);
		expect(store.isVisible).toBe(false);
	});

	test('toggles sidebar size', (): void => {
		expect.assertions(4);
		const store = useSidebarStore();
		expect(store.minimized).toBe(false);
		expect(store.isMinimized).toBe(false);
		store.toggleSize();
		expect(store.minimized).toBe(true);
		expect(store.isMinimized).toBe(true);
	});

	test('toggles sidebar visibility', (): void => {
		expect.assertions(4);
		const store = useSidebarStore();
		expect(store.visible).toBe(true);
		expect(store.isVisible).toBe(true);
		store.toggleVisibility();
		expect(store.visible).toBe(false);
		expect(store.isVisible).toBe(false);
	});

});
