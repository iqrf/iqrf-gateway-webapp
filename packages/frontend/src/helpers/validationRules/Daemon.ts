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
import {ValidationRuleFunction} from 'vee-validate/dist/types/types';

/**
 * Validates that JSON document string contains messageType property
 * @param json Document to validate
 * @returns {boolean} True if JSON document contains mType property
 */
const mType: ValidationRuleFunction = (json: string): boolean => {
	const object = JSON.parse(json);
	return {}.hasOwnProperty.call(object, 'mType');
};

export {mType};
