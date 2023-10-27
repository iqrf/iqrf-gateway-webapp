import { type Feature, type UserRole } from '@iqrf/iqrf-gateway-webapp-client/types';

/**
 * Link target enum
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
	feature?: Feature;
	/// Roles
	roles?: UserRole[];
	/// Link target
	target?: LinkTarget;
}
