/**
 * Copyright 2017-2023 IQRF Tech s.r.o.
 * Copyright 2019-2023 MICRORISC s.r.o.
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
import VueI18n from 'vue-i18n';
import {FileFormat} from '@/iqrfNet/fileFormat';
import {SecurityFormat} from '@/iqrfNet/securityFormat';

/**
 * Coreui DataTable field interface
 */
export interface IField {
	key: string
	label: VueI18n.TranslateResult
	_classes?: string|string[]|object
	_style?: string|Array<string>|object
	filter?: boolean
	sorter?: boolean
}

/**
 * Coreui select component option interface
 */
export interface IOption {
	value: FileFormat|SecurityFormat|string|number|boolean|null
	label: VueI18n.TranslateResult|string
	props?: IProps
}

/**
 * Coreui shared properties
 */
export interface IProps {
	description: string
}
