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
import store from '@/store';
import {UserRoleIndex} from '@/services/AuthenticationService';

/**
 * Link target enum
 */
export enum LinkTarget {
	blank = '_blank',
	self = '_self',
}

/**
 * Link entity
 */
export interface Link {
	/**
	 * Link title
	 */
	title: string;

	/**
	 * Link description
	 */
	description: string;

	/**
	 * Link location
	 */
	to?: string;

	/**
	 * Link external location
	 */
	href?: string;

	/**
	 * Link feature
	 */
	feature?: string;

	/**
	 * Link role
	 */
	role?: UserRoleIndex|Array<UserRoleIndex>;

	/**
	 * Link target
	 */
	target?: LinkTarget;
}

/**
 * Disambiguation helper
 */
export default class DisambiguationHelper {

	/**
	 * Filters links by role and feature
	 * @param {Link} link Link to filter
	 * @param {UserRoleIndex} role Role to filter by
	 */
	public static filter(link: Link, role: UserRoleIndex): boolean {
		if (link.role !== undefined && ((Array.isArray(link.role) && link.role.indexOf(role) === -1) || role > (link.role as UserRoleIndex))) {
			return false;
		}
		return !(link.feature !== undefined && !store.getters['features/isEnabled'](link.feature));
	}

}
