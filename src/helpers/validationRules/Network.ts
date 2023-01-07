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
import {ValidationRuleFunction} from 'vee-validate/dist/types/types';

// GSM

const apn: ValidationRuleFunction = (apn: string): boolean => {
	return /^[a-z0-9.-]+$/.test(apn);
};

const pin: ValidationRuleFunction = (pin: string): boolean => {
	return /^\d{4,8}$/.test(pin);
};

// IPv4

const subnetMask: ValidationRuleFunction = (mask: string): boolean => {
	const maskTokens = mask.split('.');
	const binaryMask = maskTokens.map((token: string) => {
		return parseInt(token).toString(2).padStart(8, '0');
	}).join('');
	return new RegExp(/^1{8,32}0{0,24}$/).test(binaryMask);
};

// WiFi

const wepKey64Bit: ValidationRuleFunction = (key: string): boolean => {
	return /^(\w{5}|[0-9a-fA-F]{10})$/.test(key);
};

const wepKey128Bit: ValidationRuleFunction = (key: string): boolean => {
	return /^(\w{13}|[0-9a-fA-F]{26})$/.test(key);
};

// Wireguard

const wgBase64Key: ValidationRuleFunction = (key: string): boolean => {
	return /^[0-9a-zA-Z+/]{43}=$/.test(key);
};

export {apn, pin, subnetMask, wepKey64Bit, wepKey128Bit, wgBase64Key};
