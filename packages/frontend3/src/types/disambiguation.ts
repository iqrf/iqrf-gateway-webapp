import { UserRole } from '@iqrf/iqrf-gateway-webapp-client/types';

/**
 * Link taget enum
 */
export enum LinkTarget {
	blank = '_blank',
	self = '_self',
}

/**
 * Link entity interface
 */
export interface DisambiguationLink {
	/// Link title
	title: string;
	/// Link description
	description: string;
	/// Link location
	to?: string;
	/// Link external location
	href?: string;
	/// Feature
	feature?: string;
	/// Roles
	roles?: UserRole[];
	/// Link target
	target?: LinkTarget;
}
