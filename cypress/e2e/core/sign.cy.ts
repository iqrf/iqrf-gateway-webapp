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

context('Sign in and sign out', () => {

	it('Sign in (invalid credentials)', () => {
		cy.visit('/sign/in');
		cy.location().should((location) => {
			expect(location.hash).to.be.empty;
			expect(location.pathname).to.eq('/sign/in');
			expect(location.search).to.be.empty;
		});
		cy.get('#username')
			.type('admin')
			.should('have.value', 'admin');
		cy.get('#password')
			.type('admin')
			.should('have.value', 'admin');
		cy.get('button[type=\'submit\']')
			.click();
		cy.toast('error', 'The username or password you entered is incorrect.');
		cy.location().should((location) => {
			expect(location.hash).to.be.empty;
			expect(location.pathname).to.eq('/sign/in');
			expect(location.search).to.be.empty;
		});

	});

	it('Sign in (valid credentials)', () => {
		cy.visit('/sign/in');
		cy.location().should((location) => {
			expect(location.hash).to.be.empty;
			expect(location.pathname).to.eq('/sign/in');
			expect(location.search).to.be.empty;
		});
		cy.get('#username')
			.type('admin')
			.should('have.value', 'admin');
		cy.get('#password')
			.type('iqrf')
			.should('have.value', 'iqrf');
		cy.get('button[type=\'submit\']')
			.click();
		cy.toast('success', 'You have been signed in successfully.');
		cy.location().should((location) => {
			expect(location.hash).to.be.empty;
			expect(location.pathname).to.eq('/');
			expect(location.search).to.be.empty;
		});
	});

	it('Sign out', () => {
		cy.signIn('admin', 'iqrf');
		cy.visit('/');
		cy.location().should((location) => {
			expect(location.hash).to.be.empty;
			expect(location.pathname).to.eq('/');
			expect(location.search).to.be.empty;
		});
		cy.get('#user-menu-button')
			.click();
		cy.get('a.dropdown-item')
			.contains('Sign out')
			.click();
		cy.toast('success', 'You have been signed out.');
		cy.location().should((location) => {
			expect(location.hash).to.be.empty;
			expect(location.pathname).to.eq('/sign/in');
			expect(location.search).to.be.empty;
		});
	});

});
