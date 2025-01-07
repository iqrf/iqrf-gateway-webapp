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
import ip from 'ip-regex';
import isFQDN from 'is-fqdn';
import punycode from 'punycode/';
import {email as veeValidateEmail} from 'vee-validate/dist/rules';
import {ValidationRuleFunction} from 'vee-validate/dist/types/types';

/**
 * Validates email address
 * @param addr Email address to validate
 * @return {boolean} True if the email address is valid
 */
const email: ValidationRuleFunction = (addr: string): boolean => {
	const encoded = punycode.toASCII(addr);
	if (!veeValidateEmail.validate(encoded)) {
		return false;
	}
	const domain = encoded.split('@');
	if (domain.length === 1) {
		return false;
	}
	return isFQDN(domain[1]);
};

/**
 * Validates host
 * @param host Host to validate
 * @return {boolean} True if the host is valid
 */
const host: ValidationRuleFunction = (host: string): boolean => {
	if (ip.v4({exact: true}).test(host)) {
		return true;
	}
	if (ip.v6({exact: true}).test(host)) {
		return true;
	}
	const encoded = punycode.toASCII(host);
	return encoded === 'localhost' || isFQDN(encoded);
};

/**
 * Validates UUID version 4
 * @param id ID to validate
 * @returns {boolean} True if ID is UUIDv4 compliant
 */
const uuid_v4: ValidationRuleFunction = (id: string): boolean => {
	const re = RegExp(/^[0-9a-f]{8}-[0-9a-f]{4}-4[0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/);
	return re.test(id);
};

/**
 * Validates IQRF Gateway Daemon's component instance name
 * @param {string} name Component instance name to validate
 * @return {boolean} True if the component instance name is valid
 */
const daemonInstanceName: ValidationRuleFunction = (name: string): boolean => {
	return /^[^&]+$/.test(name);
};

/**
 * Validates IPv4 address
 * @param {string} address IPv4 address to validate
 * @return {boolean} True if the IPv4 address is valid
 */
const ipv4: ValidationRuleFunction = (address: string): boolean => {
	return ip.v4({exact: true}).test(address);
};

/**
 * Validates IPv6 address
 * @param {string} address IPv6 address to validate
 * @return {boolean} True if the IPv6 address is valid
 */
const ipv6: ValidationRuleFunction = (address: string): boolean => {
	return ip.v6({exact: true}).test(address);
};

export {daemonInstanceName, email, host, ipv4, ipv6, uuid_v4};
