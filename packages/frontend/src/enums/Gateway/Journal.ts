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
/**
 * Systemd journal storage methods
 */
export enum Persistence {
	PERSISTENT = 'persistent',
	VOLATILE = 'volatile'
}

/**
 * Systemd journal time-based persistence units
 */
export enum TimeUnit {
	SECONDS = 's',
	MINUTES = 'm',
	HOURS = 'h',
	DAYS = 'day',
	WEEKS = 'week',
	MONTHS = 'month',
	YEAR = 'year'
}