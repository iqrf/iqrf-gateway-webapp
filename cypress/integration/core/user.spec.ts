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

const faker = require('faker');

context('User management', () => {

	it('Add a new user', () => {
		cy.visit('/');
		cy.signIn('admin', 'iqrf');
		cy.visit('/');
		cy.location().should((location) => {
			expect(location.hash).to.be.empty;
			expect(location.pathname).to.eq('/');
			expect(location.search).to.be.empty;
		});
		cy.get('ul.c-sidebar-nav > li.c-sidebar-nav-item > a.c-sidebar-nav-link')
			.contains('User manager')
			.click();
		cy.location().should((location) => {
			expect(location.hash).to.be.empty;
			expect(location.pathname).to.eq('/user/');
			expect(location.search).to.be.empty;
		});
		cy.get('div.card > header.card-header > a.btn-success')
			.contains('Add')
			.click();
		cy.location().should((location) => {
			expect(location.hash).to.be.empty;
			expect(location.pathname).to.eq('/user/add/');
			expect(location.search).to.be.empty;
		});
		const username = faker.internet.userName();
		const password = faker.internet.password();
		cy.get('#username')
			.type(username)
			.should('have.value', username)
			.blur()
			.should('have.class', 'is-valid');
		cy.get('#password')
			.type(password)
			.should('have.value', password)
			.blur()
			.should('have.class', 'is-valid');
		cy.get('button[type=\'submit\']')
			.click();
		cy.get('.v-toast--top > .v-toast__item--success > .v-toast__text')
			.contains('User has been added successfully.');
		cy.location().should((location) => {
			expect(location.hash).to.be.empty;
			expect(location.pathname).to.eq('/user/');
			expect(location.search).to.be.empty;
		});
	});

	it('Add a new user (empty username)', () => {
		cy.visit('/');
		cy.signIn('admin', 'iqrf');
		cy.visit('/user/add/');
		cy.location().should((location) => {
			expect(location.hash).to.be.empty;
			expect(location.pathname).to.eq('/user/add/');
			expect(location.search).to.be.empty;
		});
		const password = faker.internet.password();
		cy.get('#username')
			.focus()
			.should('not.have.value')
			.blur()
			.should('have.class', 'is-invalid');
		cy.get('#username').parent()
			.children('div.invalid-feedback')
			.contains('Please enter the username.');
		cy.get('#password')
			.type(password)
			.should('have.value', password)
			.blur()
			.should('have.class', 'is-valid');
		cy.get('button[type=\'submit\']')
			.should('have.attr', 'disabled');
	});

	it('Add a new user (empty password)', () => {
		cy.visit('/');
		cy.signIn('admin', 'iqrf');
		cy.visit('/user/add/');
		cy.location().should((location) => {
			expect(location.hash).to.be.empty;
			expect(location.pathname).to.eq('/user/add/');
			expect(location.search).to.be.empty;
		});
		const username = faker.internet.userName();
		cy.get('#username')
			.type(username)
			.should('have.value', username)
			.blur()
			.should('have.class', 'is-valid');
		cy.get('#password')
			.focus()
			.should('not.have.value')
			.blur()
			.should('have.class', 'is-invalid');
		cy.get('#password').parent()
			.children('div.invalid-feedback')
			.contains('Please enter the password.');
		cy.get('button[type=\'submit\']')
			.should('have.attr', 'disabled');
	});

	it('Add a new user (with already used username)', () => {
		cy.visit('/');
		cy.signIn('admin', 'iqrf');
		cy.visit('/user/add/');
		cy.location().should((location) => {
			expect(location.hash).to.be.empty;
			expect(location.pathname).to.eq('/user/add/');
			expect(location.search).to.be.empty;
		});
		const username = 'admin';
		const password = faker.internet.password();
		cy.get('#username')
			.type(username)
			.should('have.value', username)
			.blur()
			.should('have.class', 'is-valid');
		cy.get('#password')
			.type(password)
			.should('have.value', password)
			.blur()
			.should('have.class', 'is-valid');
		cy.get('button[type=\'submit\']')
			.click();
		cy.get('.v-toast--top > .v-toast__item--error > .v-toast__text')
			.contains('Failed to create new user: Username is already used');
		cy.location().should((location) => {
			expect(location.hash).to.be.empty;
			expect(location.pathname).to.eq('/user/add/');
			expect(location.search).to.be.empty;
		});
	});

});
