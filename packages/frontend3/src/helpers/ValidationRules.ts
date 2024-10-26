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

import isFQDN from 'is-fqdn';
import { toASCII as punycodeToASCII } from 'punycode/';
import { z } from 'zod';

/**
 * Validation rules for forms
 */
export default class ValidationRules {

	/**
	 * Required field
	 * @param {unknown} value Field value
	 * @param {string} error Error message
	 * @return {boolean|string} Validation result
	 */
	public static required(value: unknown, error: string): boolean | string {
		return (ValidationRules.isEmpty(value) || value === false) ? error : true;
	}

	/**
	 * Required field if condition is true
	 * @param {unknown} value Field value
	 * @param {boolean} condition Condition
	 * @param {string} error Error message
	 * @return {boolean|string} Validation result
	 */
	public static requiredIf(value: unknown, condition: boolean, error: string): boolean | string {
		if (!condition) {
			return true;
		}
		return ValidationRules.required(value, error);
	}

	/**
	 * Minimum value
	 * @param {number} value Field value
	 * @param {number} min Minimum value
	 * @param {string} error Error message
	 * @return {boolean|string} Validation result
	 */
	public static min(value: number, min: number, error: string): boolean | string {
		return (value >= min) || error;
	}

	/**
	 * Maximum value
	 * @param {number} value Field value
	 * @param {number} max Maximum value
	 * @param {string} error Error message
	 * @return {boolean|string} Validation result
	 */
	public static max(value: number, max: number, error: string): boolean | string {
		return (value <= max) || error;
	}

	/**
	 * Value between min and max
	 * @param {number} value Field value
	 * @param {number} min Minimum value
	 * @param {number} max Maximum value
	 * @param {string} error Error message
	 * @return {boolean|string} Validation result
	 */
	public static between(value: number, min: number, max: number, error: string): boolean | string {
		return (value >= min && value <= max) || error;
	}

	/**
	 * Length of value
	 * @param {string|unknown[]} value Field value
	 * @param {number} length Length
	 * @param {string} error Error message
	 * @return {boolean|string} Validation result
	 */
	public static len(value: string | unknown[], length: number, error: string): boolean | string {
		return (value.length === length) || error;
	}

	/**
	 * Minimum length of value
	 * @param {string|unknown[]} value Field value
	 * @param {number} length Minimum length
	 * @param {string} error Error message
	 * @return {boolean|string} Validation result
	 */
	public static minLength(value: string | unknown[], length: number, error: string): boolean | string {
		return (value.length >= length) || error;
	}

	/**
	 * Maximum length of value
	 * @param {string|unknown[]} value Field value
	 * @param {number} length Maximum length
	 * @param {string} error Error message
	 * @return {boolean|string} Validation result
	 */
	public static maxLength(value: string | unknown[], length: number, error: string): boolean | string {
		return (value.length <= length) || error;
	}

	/**
	 * E-mail address
	 * @param {string} value Field value
	 * @param {string} errorMessage Error message
	 * @return {boolean|string} Validation result
	 */
	public static email(value: string, errorMessage: string): boolean | string {
		const validator: z.ZodString = z.string().email();
		return validator.safeParse(punycodeToASCII(value)).success || errorMessage;
	}

	/**
	 * Integer
	 * @param {number} value Field value
	 * @param {string} error Error message
	 * @return {boolean|string} Validation result
	 */
	public static integer(value: number, error: string): boolean | string {
		return Number.isInteger(value) || error;
	}

	/**
	 * Numerical value
	 * @param {unknown} value Field value
	 * @param {string} error Error message
	 * @return {boolean|string} Validation result
	 */
	public static numerical(value: unknown, error: string): boolean | string {
		if (typeof value === 'number' && !Number.isNaN(value)) {
			return true;
		}
		return error;
	}

	/**
	 * IPv4 address
	 * @param {string} value Field value
	 * @param {string} error Error message
	 * @return {boolean|string} Validation result
	 */
	public static ipv4Address(value: string, error: string): boolean | string {
		if (ValidationRules.isEmpty(value)) {
			return true;
		}
		const ipv4Validator: z.ZodString = z.string().ip({ version: 'v4' });
		if (ipv4Validator.safeParse(value).success) {
			return true;
		}
		return error;
	}

	/**
	 * IPv6 address
	 * @param {string} value Field value
	 * @param {string} error Error message
	 * @return {boolean|string} Validation result
	 */
	public static ipv6Address(value: string, error: string): boolean | string {
		if (ValidationRules.isEmpty(value)) {
			return true;
		}
		const ipv4Validator: z.ZodString = z.string().ip({ version: 'v6' });
		if (ipv4Validator.safeParse(value).success) {
			return true;
		}
		return error;
	}

	/**
	 * Server address
	 * @param {string|null} value Field value
	 * @param {string} error Error message
	 * @return {boolean|string} Validation result
	 */
	public static server(value: string | null, error: string): boolean | string {
		if (value === null) {
			return error;
		}
		const ipv4Validator: z.ZodString = z.string().ip({ version: 'v4' });
		const ipv6Validator: z.ZodString = z.string().ip({ version: 'v6' });
		return (ipv4Validator.safeParse(value).success || ipv6Validator.safeParse(value).success || value === 'localhost' || isFQDN(value)) || error;
	}

	/**
	 * UUID
	 * @param {string|null} value Field value
	 * @param {string} error Error message
	 * @return {boolean|string} Validation result
	 */
	public static uuid(value: string | null, error: string): boolean | string {
		if (value === null) {
			return error;
		}
		const uuidValidator: z.ZodString = z.string().uuid();
		return uuidValidator.safeParse(value).success || error;
	}

	/**
	 * JSON object
	 * @param {string|null} value Field value
	 * @param {string} error Error message
	 * @return {boolean|string} Validation result
	 */
	public static json(value: string | null, error: string): boolean | string {
		if (value === null) {
			return error;
		}
		try {
			JSON.parse(value);
			return true;
		} catch {
			return error;
		}
	}

	/**
	 * Regular expression
	 * @param {string | null} value Field value
	 * @param {RegExp} pattern Regular expression pattern
	 * @param {string} error Error message
	 * @return {boolean|string} Validation result
	 */
	public static regex(value: string | null, pattern: RegExp, error: string): boolean | string {
		if (value === null) {
			return true;
		}
		return pattern.test(value) || error;
	}

	/**
	 * URL validator
	 * @param {string | null} value Field value
	 * @param {string} error Error message
	 * @return {boolean|string} Validation result
	 */
	public static url(value: string | null, error: string): boolean | string {
		if (value === null) {
			return error;
		}
		const urlValidator: z.ZodString = z.string().url();
		return urlValidator.safeParse(value).success || error;
	}

	/**
	 * Checks if the value is empty
	 * @param {unknown} value Field value
	 * @return {boolean} Value emptiness
	 */
	private static isEmpty(value: unknown): boolean {
		return (
			value === null ||
			value === undefined ||
			value === false ||
			(Array.isArray(value) && value.length === 0) ||
			(typeof value === 'object' && Object.keys(value).length === 0) ||
			(typeof value === 'string' && value.trim().length === 0)
		);
	}

}
