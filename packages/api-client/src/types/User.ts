/**
 * Copyright 2023-2025 MICRORISC s.r.o.
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

import { type Language } from '@iqrf/iqrf-ui-common-types';

/**
 * Account state enum
 */
export enum AccountState {
	/// Blocked account
	Blocked = 'blocked',
	/// Invited account
	Invited = 'invited',
	/// Unverified e-mail address
	Unverified = 'unverified',
	/// Verified e-mail address
	Verified = 'verified',
}

export interface WithBaseUrl {
	/// IQRF Gateway Webapp frontend base URL
	baseUrl?: string;
}

/**
 * User preferences
 */
export interface UserPreferences {
	/// Time format
	timeFormat: UserTimeFormatPreference;
	/// Theme
	theme: UserThemePreference;
}

/**
 * User role enum
 */
export enum UserRole {
	/// Administrator
	Admin = 'admin',
	/// Basic user
	Basic = 'basic',
	/// Normal user
	Normal = 'normal',
}

/**
 * User base interface
 */
export interface UserBase {
	/// User e-mail address
	email: string|null;
	/// User language
	language: Language;
	/// User role
	role: UserRole;
	/// User name
	username: string;
}

/**
 * User create interface
 */
export interface UserCreate extends UserBase, WithBaseUrl {
	/// User password
	password: string;
}

/**
 * User edit interface
 */
export interface UserEdit extends UserBase, WithBaseUrl {
	/// User password
	password?: string;
}

/**
 * User info interface
 */
export interface UserInfo extends UserBase {
	/// User ID
	id: number;
	/// User account state
	state: AccountState;
}

/**
 * User signed in interface
 */
export interface UserSignedIn extends UserInfo {
	/// User JWT token
	token: string;
}

/**
 * Current user edit profile
 */
export interface AccountEdit extends WithBaseUrl {
	username?: string;
	language?: Language;
	email?: string;
}

/**
 * User password change interface
 */
export interface UserPasswordChange extends WithBaseUrl {
	/// New user password
	new: string;
	/// Current user password
	old: string;
}

/**
 * User password reset interface
 */
export interface UserPasswordReset extends WithBaseUrl {
	/// New user password
	password: string;
}

/**
 * User password set interface
 */
export interface UserPasswordSet {
	/// New user password
	password: string;
}

/**
 * User account recovery interface
 */
export interface UserAccountRecovery extends WithBaseUrl {
	/// Username
	username: string;
}

/**
 * E-mail address verification resend request interface
 */
export type EmailVerificationResendRequest = WithBaseUrl;

/**
 * User theme preference
 */
export enum UserThemePreference {
	/// Automatic theme detection based on system settings
	Auto = 'auto',
	/// Light theme
	Light = 'light',
	/// Dark theme
	Dark = 'dark',
}

/**
 * User time format preference
 */
export enum UserTimeFormatPreference {
	/// Automatic time format detection based on system settings
	Auto = 'auto',
	/// 12-hour format (AM/PM)
	Hour12 = '12h',
	/// 24-hour format
	Hour24 = '24h',
}
