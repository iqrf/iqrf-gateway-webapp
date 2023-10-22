/**
 * Copyright 2023-2024 MICRORISC s.r.o.
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
 * Account state enum
 */
export enum AccountState {
	/// Unverified account
	Unverified = 'unverified',
	/// Verified account
	Verified = 'verified',
}

/**
 * User language enum
 */
export enum UserLanguage {
	/// Czech
	Czech = 'cs',
	/// English
	English = 'en',
}

/**
 * User role enum
 */
export enum UserRole {
	/// Administrator
	Admin = 'admin',
	/// Basic user
	Basic = 'basic',
	/// Basic user with user management
	BasicAdmin = 'basicadmin',
	/// Normal user
	Normal = 'normal'
}

/**
 * User session expiration enum
 */
export enum UserSessionExpiration {
	/// Day
	Day = 'day',
	/// Default expiration (90 min)
	Default = 'default',
	/// Month
	Month = 'month',
	/// Week
	Week = 'week',
	/// Year
	Year = 'year'
}

/**
 * User base interface
 */
export interface UserBase {
	/// User e-mail address
	email: string|null;
	/// User language
	language: UserLanguage;
	/// User role
	role: UserRole;
	/// User name
	username: string;
}

/**
 * User create interface
 */
export interface UserCreate extends UserBase {
	/// Base URL
	baseUrl?: string;
	/// User password
	password: string;
}

/**
 * User edit interface
 */
export interface UserEdit extends UserBase {
	/// Base URL
	baseUrl?: string;
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
 * User password change interface
 */
export interface UserPasswordChange {
	/// Base URL
	baseUrl?: string;
	/// New user password
	new: string;
	/// Current user password
	old: string;
}

/**
 * User account recovery interface
 */
export interface UserAccountRecovery {
	/// Base URL
	baseUrl?: string;
	/// Username
	username: string;
}
