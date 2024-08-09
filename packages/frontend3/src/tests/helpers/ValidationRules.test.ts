/**
 * Copyright 2017-2024 IQRF Tech s.r.o.
 * Copyright 2019-2024 MICRORISC s.r.o.
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

import { beforeEach, describe, expect, it, vi } from 'vitest';

import ValidationRules from '../../helpers/ValidationRules';

describe('ValidationRules', (): void => {

	/**
	 * Restore all mocks before each test
	 */
	beforeEach((): void => {
		vi.restoreAllMocks();
		vi.unstubAllEnvs();
		vi.unstubAllGlobals();
	});

	it('required field', (): void => {
		expect.assertions(8);
		const error = 'This field is required';
		expect(ValidationRules.required(null, error)).toStrictEqual(error);
		expect(ValidationRules.required(undefined, error)).toStrictEqual(error);
		expect(ValidationRules.required([], error)).toStrictEqual(error);
		expect(ValidationRules.required('', error)).toStrictEqual(error);
		expect(ValidationRules.required(false, error)).toStrictEqual(error);
		expect(ValidationRules.required(true, error)).toBeTruthy();
		expect(ValidationRules.required(['foobar'], error)).toBeTruthy();
		expect(ValidationRules.required('foobar', error)).toBeTruthy();
	});

	it('required field if condition is true', (): void => {
		expect.assertions(16);
		const error = 'This field is required';
		expect(ValidationRules.requiredIf(null, true, error)).toStrictEqual(error);
		expect(ValidationRules.requiredIf(undefined, true, error)).toStrictEqual(error);
		expect(ValidationRules.requiredIf([], true, error)).toStrictEqual(error);
		expect(ValidationRules.requiredIf('', true, error)).toStrictEqual(error);
		expect(ValidationRules.requiredIf(false, true, error)).toStrictEqual(error);
		expect(ValidationRules.requiredIf(true, true, error)).toBeTruthy();
		expect(ValidationRules.requiredIf(['foobar'], true, error)).toBeTruthy();
		expect(ValidationRules.requiredIf('foobar', true, error)).toBeTruthy();

		expect(ValidationRules.requiredIf(null, false, error)).toBeTruthy();
		expect(ValidationRules.requiredIf(undefined, false, error)).toBeTruthy();
		expect(ValidationRules.requiredIf([], false, error)).toBeTruthy();
		expect(ValidationRules.requiredIf('', false, error)).toBeTruthy();
		expect(ValidationRules.requiredIf(false, false, error)).toBeTruthy();
		expect(ValidationRules.requiredIf(true, false, error)).toBeTruthy();
		expect(ValidationRules.requiredIf(['foobar'], false, error)).toBeTruthy();
		expect(ValidationRules.requiredIf('foobar', false, error)).toBeTruthy();
	});

	it('maximal value', (): void => {
		expect.assertions(3);
		const error = 'This field must contain a number lower than 8.';
		expect(ValidationRules.max(0, 8, error)).toBeTruthy();
		expect(ValidationRules.max(8, 8, error)).toBeTruthy();
		expect(ValidationRules.max(16, 8, error)).toStrictEqual(error);
	});

	it('minimal value', (): void => {
		expect.assertions(3);
		const error = 'This field must contain a number greater than 8.';
		expect(ValidationRules.min(0, 8, error)).toStrictEqual(error);
		expect(ValidationRules.min(8, 8, error)).toBeTruthy();
		expect(ValidationRules.min(16, 8, error)).toBeTruthy();
	});

	it('between values', (): void => {
		expect.assertions(6);
		const error = 'This field must contain a number between 4 and 8.';
		expect(ValidationRules.between(0, 4, 8, error)).toStrictEqual(error);
		expect(ValidationRules.between(6, 4, 8, error)).toBeTruthy();
		expect(ValidationRules.between(16, 4, 8, error)).toStrictEqual(error);

		expect(ValidationRules.between(0, 8, 4, error)).toStrictEqual(error);
		expect(ValidationRules.between(6, 8, 4, error)).toStrictEqual(error);
		expect(ValidationRules.between(16, 8, 4, error)).toStrictEqual(error);
	});

});
