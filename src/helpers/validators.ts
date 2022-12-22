/**
 * Copyright 2017-2021 IQRF Tech s.r.o.
 * Copyright 2019-2021 MICRORISC s.r.o.
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
const host: ValidationRuleFunction = (host: string) => {
	if (ip.v4({exact: true}).test(host)) {
		return true;
	}
	if (ip.v6({exact: true}).test(host)) {
		return true;
	}
	const encoded = punycode.toASCII(host);
	return encoded === 'localhost' || isFQDN(encoded);
};

export {email, host};
