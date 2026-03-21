/**
 * Copyright 2023-2026 MICRORISC s.r.o.
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

import { type DateTime } from 'luxon';

/**
 * Mosquitto user state enum
 */
export enum MosquittoUserState {
	/**
	 * Active user
	 */
	Active,
	/**
	 * Blocked user
	 */
	Blocked,
}

/**
 * Mosquitto user create params interface
 */
export interface MosquittoUserCreate {
	/**
	 * Username
	 */
	username: string;
	/**
	 * Password
	 */
	password: string;
}

/**
 * Mosquitto user base interface
 */
export interface MosquittoUserBase {
	/**
	 * User ID
	 */
	id: number;
	/**
	 * Username
	 */
	username: string;
	/**
	 * User state
	 */
	state: MosquittoUserState;
}

/**
 * Mosquitto user RAW API response interface
 */
export interface MosquittoUserRaw extends MosquittoUserBase {
	/**
	 * Created at timestamp
	 */
	createdAt: string;
	/**
	 * Blocked at timestamp
	 */
	blockedAt: string|null;
}

/**
 * Mosquitto user parsed interface
 */
export interface MosquittoUser extends MosquittoUserBase {
	/**
	 * Created at timestamp
	 */
	createdAt: DateTime;
	/**
	 * Blocked at timestamp
	 */
	blockedAt: DateTime|null;
}
