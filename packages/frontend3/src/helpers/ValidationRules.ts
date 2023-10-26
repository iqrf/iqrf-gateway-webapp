import { toASCII as punycodeToASCII } from 'punycode/';

import { z } from 'zod';
import isFQDN from 'is-fqdn';

export default class ValidationRules {

	public static required(value: unknown, error: string): boolean | string {
		if (value === null || value === undefined || (Array.isArray(value) && value.length === 0) || value === false) {
			return error;
		}
		return String(value).trim().length > 0 || error;
	}

	public static requiredIf(value: unknown, condition: boolean, error: string): boolean | string {
		return !condition || !!value || error;
	}

	public static min(value: number, min: number, error: string): boolean | string {
		return (value >= min) || error;
	}

	public static max(value: number, max: number, error: string): boolean | string {
		return (value <= max) || error;
	}

	public static between(value: number, min: number, max: number, error: string): boolean | string {
		return (value >= min && value <= max) || error;
	}

	public static len(value: string | unknown[], length: number, error: string): boolean | string {
		return (value.length === length) || error;
	}

	public static minLength(value: string | unknown[], length: number, error: string): boolean | string {
		return (value.length >= length) || error;
	}

	public static maxLength(value: string | unknown[], length: number, error: string): boolean | string {
		return (value.length <= length) || error;
	}

	public static email(value: string, errorMessage: string): boolean | string {
		const validator = z.string().email();
		return validator.safeParse(punycodeToASCII(value)).success || errorMessage;
	}

	public static integer(value: number, error: string): boolean | string {
		return Number.isInteger(value) || error;
	}

	public static numerical(value: unknown, error: string): boolean | string {
		if (typeof value === 'number' && !Number.isNaN(value)) {
			return true;
		}
		return error;
	}

	public static server(value: string | null, error: string): boolean | string {
		if (value === null) {
			return error;
		}
		const ipv4Validator = z.string().ip({version: 'v4'});
		const ipv6Validator = z.string().ip({version: 'v6'});
		return (ipv4Validator.safeParse(value).success || ipv6Validator.safeParse(value).success || value === 'localhost' || isFQDN(value)) || error;

	}
}
