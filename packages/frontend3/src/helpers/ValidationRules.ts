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
		if (value === null || value === undefined || (Array.isArray(value) && value.length === 0) || value === false) {
			return error;
		}
		return String(value).trim().length > 0 || error;
	}

	/**
	 * Required field if condition is true
	 * @param {unknown} value Field value
	 * @param {boolean} condition Condition
	 * @param {string} error Error message
	 * @return {boolean|string} Validation result
	 */
	public static requiredIf(value: unknown, condition: boolean, error: string): boolean | string {
		return !condition || !!ValidationRules.required(value, error);
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
	 * Server address
	 * @param {string|null} value Field value
	 * @param {string} error Error message
	 * @return {boolean|string} Validation result
	 */
	public static server(value: string | null, error: string): boolean | string {
		if (value === null) {
			return error;
		}
		const ipv4Validator: z.ZodString = z.string().ip({version: 'v4'});
		const ipv6Validator: z.ZodString = z.string().ip({version: 'v6'});
		return (ipv4Validator.safeParse(value).success || ipv6Validator.safeParse(value).success || value === 'localhost' || isFQDN(value)) || error;

	}
}
