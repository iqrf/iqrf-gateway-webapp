/**
 * Copyright 2021 Roman Ondráček <xondra58@stud.fit.vutbr.cz>
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *      http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

/// <reference types="cypress" />
declare namespace Cypress {
	interface Chainable {
		/**
		 * Custom command to sign in
		 * @example cy.signIn('username', 'password)
		 */
		signIn(username: string, password: string): Chainable<any>

		/**
		 * Checks the toast message
		 */
		toast(type: string, message: string): Chainable<any>

		/**
		 * Checks if the text input is valid
		 */
		validTextInput(selector: string): Chainable<any>

		/**
		 * Checks if the text input is invalid
		 */
		invalidTextInput(selector: string, errorMessage: string): Chainable<any>
	}
}
