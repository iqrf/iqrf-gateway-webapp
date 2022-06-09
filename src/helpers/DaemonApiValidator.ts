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
import {AdditionalPropertiesParams, ErrorObject} from 'ajv';
import validate from './validate_daemonRequest';
import i18n from '@/i18n';

export interface DaemonApiValidatorCallback {
	(errorMessages: Array<string>): void;
}

/**
 * Daemon JSON API validator
 */
export default class DaemonApiValidator {

	/**
	 * Constructor
	 */
	constructor() {
		this.validator = validate;
	}

	/**
	 * @var validator JSON schema validator function
	 */
	private readonly validator: any = null;

	/**
	 * Validates the JSON against Daemon JSON API schema
	 * @param {string} json JSON to validate
	 * @param {DaemonApiValidatorCallback} callback Callback for retrieving JSON schema violations
	 * @return {boolean} Is the JSON valid?
	 */
	public validate(json: string, callback: DaemonApiValidatorCallback): boolean {
		let errors: Array<string> = [];
		try {
			const jsonObject = JSON.parse(json);
			if (this.validator(jsonObject)) {
				return true;
			} else {
				console.warn(this.validator.errors);
				errors = this.buildViolationString(this.validator.errors);
				return false;
			}
		} catch (error) {
			return false;
		} finally {
			callback(errors);
		}
	}

	/**
	 * Creates a JSON schema violation string message
	 * @param {Array<ErrorObject>} errors Array of violations
	 * @return {Array<string>} JSON schema violations strings
	 */
	private buildViolationString(errors: Array<ErrorObject>): Array<string> {
		const messages: Array<string> = [];
		for (const error of errors) {
			let message = '';
			if (error.keyword === 'type') {
				message = i18n.t(
					'iqrfnet.sendJson.violations.type',
					{
						property: error.dataPath,
						message: error.message,
						path: error.schemaPath,
						type: (typeof error.data),
					}
				).toString();
			} else if (error.keyword === 'additionalProperties') {
				message = i18n.t(
					'iqrfnet.sendJson.violations.additional',
					{
						object: error.dataPath,
						message: error.message,
						path: error.schemaPath,
						property: (error.params as AdditionalPropertiesParams).additionalProperty,
					}
				).toString();
			} else if (error.keyword === 'required') {
				message = i18n.t(
					'iqrfnet.sendJson.violations.required',
					{
						object: (error.dataPath.length === 0 ? 'root' : error.dataPath),
						message: error.message,
						path: error.schemaPath,
					}
				).toString();
			} else if (error.keyword === 'minimum' || error.keyword === 'maximum') {
				message = i18n.t(
					'iqrfnet.sendJson.violations.range',
					{
						property: error.dataPath,
						message: error.message,
						path: error.schemaPath,
						value: error.data,
					}
				).toString();
			}
			messages.push(message.trimRight());
		}
		return messages;
	}
}
