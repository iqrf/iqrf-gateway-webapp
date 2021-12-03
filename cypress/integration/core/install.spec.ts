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

context('Installation wizard', () => {

	it('Introduction', () => {
		cy.visit('/');
		cy.location().should((location) => {
			expect(location.hash).to.be.empty;
			expect(location.pathname).to.eq('/install/');
			expect(location.search).to.be.empty;
		});
		cy.get('.progress__wrapper')
			.children('span.progress__block')
			.children('span.progress__label')
			.should('have.length', 3)
			.each(((element, index, $list) => {
				switch (index) {
					case 0:
						cy.wrap(element).contains('Introduction')
							.should('have.css', 'color', 'rgb(51, 122, 183)');
						break;
					case 1:
						cy.wrap(element).contains('Create webapp user')
							.should('have.css', 'color', 'rgb(158, 158, 158)');
						break;
					case 2:
						cy.wrap(element).contains('Configure SMTP server')
							.should('have.css', 'color', 'rgb(158, 158, 158)');
						break;
				}
			}));
		cy.get('.card-body')
			.contains('Welcome to IQRF Gateway Webapp. Before getting started, you have to create the webapp\'s first user.');
		cy.get('.btn')
			.contains('Create a new user')
			.click();
		cy.location().should((location) => {
			expect(location.hash).to.be.empty;
			expect(location.pathname).to.eq('/install/webapp-user/');
			expect(location.search).to.be.empty;
		});
	});

	it('Create user', () => {
		cy.visit('/install/webapp-user/');
		cy.location().should((location) => {
			expect(location.hash).to.be.empty;
			expect(location.pathname).to.eq('/install/webapp-user/');
			expect(location.search).to.be.empty;
		});
		cy.get('.progress__wrapper')
			.children('span.progress__block')
			.children('span.progress__label')
			.should('have.length', 3)
			.each(((element, index, $list) => {
				switch (index) {
					case 0:
						cy.wrap(element).contains('Introduction')
							.should('have.css', 'color', 'rgb(123, 167, 205)');
						break;
					case 1:
						cy.wrap(element).contains('Create webapp user')
							.should('have.css', 'color', 'rgb(51, 122, 183)');
						break;
					case 2:
						cy.wrap(element).contains('Configure SMTP server')
							.should('have.css', 'color', 'rgb(158, 158, 158)');
						break;
				}
			}));
		const username = 'admin';
		const email = 'admin@iqrf.org';
		const password = 'iqrf';
		cy.get('#username')
			.type(username)
			.should('have.value', username)
			.blur()
			.should('have.class', 'is-valid');
		cy.get('#email')
			.type(email)
			.should('have.value', email)
			.blur()
			.should('have.class', 'is-valid');
		cy.get('#password')
			.type(password)
			.should('have.value', password)
			.blur()
			.should('have.class', 'is-valid');
		cy.get('.btn')
			.contains('Create user')
			.click();
		cy.get('.v-toast--top > .v-toast__item--success > .v-toast__text')
			.contains('An email containing instructions to verify your account was sent to your address.');
		cy.location().should((location) => {
			expect(location.hash).to.be.empty;
			expect(location.pathname).to.eq('/install/smtp/');
			expect(location.search).to.be.empty;
		});
	});

	it('Configure SMTP server', () => {
		cy.location().should((location) => {
			expect(location.hash).to.be.empty;
			expect(location.pathname).to.eq('/install/smtp/');
			expect(location.search).to.be.empty;
		});
		cy.get('.progress__wrapper')
			.children('span.progress__block')
			.children('span.progress__label')
			.should('have.length', 3)
			.each(((element, index, $list) => {
				switch (index) {
					case 0:
						cy.wrap(element).contains('Introduction')
							.should('have.css', 'color', 'rgb(123, 167, 205)');
						break;
					case 1:
						cy.wrap(element).contains('Create webapp user')
							.should('have.css', 'color', 'rgb(123, 167, 205)');
						break;
					case 2:
						cy.wrap(element).contains('Configure SMTP server')
							.should('have.css', 'color', 'rgb(51, 122, 183)');
						break;
				}
			}));
		cy.get('.btn')
			.contains('Skip')
			.click();
		cy.get('.v-toast--top > .v-toast__item--success > .v-toast__text')
			.contains('Installation process successfully completed.');
		cy.location().should((location) => {
			expect(location.hash).to.be.empty;
			expect(location.pathname).to.eq('/');
			expect(location.search).to.be.empty;
		});
	});

});
