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

import { describe, expect, test } from 'vitest';

import {
	AptService,
	ConfigServices,
	IqrfGatewayControllerService,
	IqrfGatewayDaemonService,
	IqrfGatewayInfluxdbBridgeService,
	IqrfRepositoryService,
	JournalService,
	MailerService,
	MenderService,
	MonitService,
} from '../../../src/services/Config';
import { mockedClient } from '../../mocks/axios';

describe('ConfigServices', (): void => {

	/**
	 * @var {ConfigServices} services Config services
	 */
	const services: ConfigServices = new ConfigServices(mockedClient);

	test('returns APT config service instance', (): void => {
		expect.assertions(1);
		expect(services.getAptService())
			.toBeInstanceOf(AptService);
	});

	test('returns IQRF Repository config service instance', (): void => {
		expect.assertions(1);
		expect(services.getIqrfRepositoryService())
			.toBeInstanceOf(IqrfRepositoryService);
	});

	test('returns IQRF Gateway Controller config service instance', (): void => {
		expect.assertions(1);
		expect(services.getIqrfGatewayControllerService())
			.toBeInstanceOf(IqrfGatewayControllerService);
	});

	test('returns IQRF Gateway Daemon config service instance', (): void => {
		expect.assertions(1);
		expect(services.getIqrfGatewayDaemonService())
			.toBeInstanceOf(IqrfGatewayDaemonService);
	});

	test('returns IQRF Gateway InfluxDB bridge config service instance', (): void => {
		expect.assertions(1);
		expect(services.getIqrfGatewayInfluxdbBridgeService())
			.toBeInstanceOf(IqrfGatewayInfluxdbBridgeService);
	});

	test('returns Journal config service instance', (): void => {
		expect.assertions(1);
		expect(services.getJournalService())
			.toBeInstanceOf(JournalService);
	});

	test('returns Mailer config service instance', (): void => {
		expect.assertions(1);
		expect(services.getMailerService())
			.toBeInstanceOf(MailerService);
	});

	test('returns Mender config service instance', (): void => {
		expect.assertions(1);
		expect(services.getMenderService())
			.toBeInstanceOf(MenderService);
	});

	test('returns Monit config service instance', (): void => {
		expect.assertions(1);
		expect(services.getMonitService())
			.toBeInstanceOf(MonitService);
	});

});
