/**
 * Copyright 2017-2024 IQRF Tech s.r.o.
 * Copyright 2019-2024 MICRORISC s.r.o.
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

import { type Feature, type UserRole } from '@iqrf/iqrf-gateway-webapp-client/types';
import { type RouteLocationRaw } from 'vue-router';

/**
 * Sidebar item
 */
export interface SidebarLink {
	/// Icon
	icon?: string;
	/// Title
	title: string;
	/// Children
	children?: SidebarLink[];
	/// Group
	group?: string | RegExp;
	/// Route
	to?: RouteLocationRaw;
	/// Href
	href?: string;
	/// Target
	target?: string;
	/// User roles
	roles?: UserRole[];
	/// Feature enabled
	feature?: Feature;
	/// Development build only?
	developmentOnly?: boolean
}
