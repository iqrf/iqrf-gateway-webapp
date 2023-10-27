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
}
