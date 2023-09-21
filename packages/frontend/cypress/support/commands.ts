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

// For more comprehensive examples of custom commands please read more here:
// https://on.cypress.io/custom-commands

import {Store} from 'vuex';
import {UserCredentials} from '@iqrf/iqrf-gateway-webapp-client/types';

Cypress.Commands.add('signIn', (username: string, password: string): Cypress.Chainable<any> => {
	cy.visit('/sign/in');
	const credentials: UserCredentials = {
		username: username,
		password: password,
	};
	const store = cy.window().its('app.$store');
	return store.then((store: Store<any>): Promise<any> => {
		if (store.getters['user/isLoggedIn']) {
			return;
		}
		return Promise.all([
			store.dispatch('user/signIn', credentials),
			store.dispatch('features/fetch'),
		]);
	});
});

Cypress.Commands.add('toast', (type: string, message: string): Cypress.Chainable<any> => {
	return cy.get(`.v-toast--top > .v-toast__item--${type} > .v-toast__text`)
		.contains(message);
});

Cypress.Commands.add('validTextInput', (selector: string): Cypress.Chainable<any> => {
	return cy.get(selector)
		.should('have.class', 'is-valid');
});

Cypress.Commands.add('invalidTextInput', (selector: string, errorMessage: string): Cypress.Chainable<any> => {
	return cy.get(selector)
		.should('have.class', 'is-invalid')
		.parent()
		.children('div.invalid-feedback')
		.contains(errorMessage);
});
