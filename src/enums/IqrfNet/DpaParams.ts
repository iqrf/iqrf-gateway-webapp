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

/**
 * DPA value action
 */
export enum DpaParamAction {
	GET = 'get',
	SET = 'set'
}

/**
 * DPA value type
 */
export enum DpaValueType {
	RSSI = 0,
	SUPPLY_VOLTAGE = 1,
	SYSTEM = 2,
	USER = 3
}

/**
 * FRC response time
 */
export enum FrcResponseTime {
	MS40 = 0,
	MS360 = 16,
	MS680 = 32,
	MS1320 = 48,
	MS2600 = 64,
	MS5160 = 80,
	MS10280 = 96,
	MS20620 = 112
}
