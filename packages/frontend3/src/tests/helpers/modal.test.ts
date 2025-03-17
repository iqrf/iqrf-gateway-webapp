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

import { beforeEach, describe, expect, test, vi } from 'vitest';
import { ref } from 'vue';

import { getModalWidth } from '@/helpers/modal';

describe('Modal helper', (): void => {

	/**
	 * Restore all mocks before each test
	 */
	beforeEach((): void => {
		vi.restoreAllMocks();
		vi.unstubAllEnvs();
		vi.unstubAllGlobals();
		vi.mock('vuetify');
	});

	test('get modal window width for large screens', async (): Promise<void> => {
		expect.assertions(1);
		const vuetify = await import('vuetify');
		vi.spyOn(vuetify, 'useDisplay').mockReturnValueOnce({
			lgAndUp: ref(true),
			md: ref(false),
		} as unknown as ReturnType<typeof vuetify.useDisplay>);
		expect(getModalWidth().value).toStrictEqual('50%');
	});

	test('get modal window width for medium screens', async (): Promise<void> => {
		expect.assertions(1);
		const vuetify = await import('vuetify');
		vi.spyOn(vuetify, 'useDisplay').mockReturnValueOnce({
			lgAndUp: ref(false),
			md: ref(true),
		} as unknown as ReturnType<typeof vuetify.useDisplay>);
		expect(getModalWidth().value).toStrictEqual('75%');
	});

	test('get modal window width for small screens', async (): Promise<void> => {
		expect.assertions(1);
		const vuetify = await import('vuetify');
		vi.spyOn(vuetify, 'useDisplay').mockReturnValueOnce({
			lgAndUp: ref(false),
			md: ref(false),
		} as unknown as ReturnType<typeof vuetify.useDisplay>);
		expect(getModalWidth().value).toStrictEqual('100%');
	});

});
