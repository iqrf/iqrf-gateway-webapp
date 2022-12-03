<!--
Copyright 2017-2021 IQRF Tech s.r.o.
Copyright 2019-2021 MICRORISC s.r.o.

Licensed under the Apache License, Version 2.0 (the "License");
you may not use this file except in compliance with the License.
You may obtain a copy of the License at

    http://www.apache.org/licenses/LICENSE-2.0

Unless required by applicable law or agreed to in writing, software,
distributed under the License is distributed on an "AS IS" BASIS,
WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied
See the License for the specific language governing permissions and
limitations under the License.
-->
<template>
	<div>
		<h1>{{ title }}</h1>
		<CCard body-wrapper>
			<div v-if='!missing && !unsupported && !unknown'>
				<CButton
					v-if='!service.enabled'
					color='success'
					@click='enable()'
				>
					{{ $t('service.actions.enable') }}
				</CButton> <CButton
					v-if='service.enabled'
					color='danger'
					@click='disable()'
				>
					{{ $t('service.actions.disable') }}
				</CButton> <CButton
					v-if='!service.active'
					color='success'
					@click='start()'
				>
					{{ $t('service.actions.start') }}
				</CButton> <CButton
					v-if='service.active'
					color='danger'
					@click='stop()'
				>
					{{ $t('service.actions.stop') }}
				</CButton> <CButton
					v-if='service.active'
					color='info'
					@click='restart()'
				>
					{{ $t('service.actions.restart') }}
				</CButton> <CButton
					color='secondary'
					@click='refreshStatus()'
				>
					{{ $t('service.actions.status') }}
				</CButton>
			</div>
			<br>
			<strong>{{ $t('service.status') }}: </strong>
			<span v-if='missing'>
				{{ $t('service.states.missing') }}
			</span>
			<span v-else-if='unsupported'>
				{{ $t('service.states.unsupported') }}
			</span>
			<span v-else-if='unknown'>
				{{ $t('service.states.unknown') }}
			</span>
			<span v-else>
				<span v-if='service.enabled'>{{ $t('states.enabled') }}</span>
				<span v-else>{{ $t('states.disabled') }}</span>,
				<span v-if='service.active'>{{ $t('service.states.active') }}</span>
				<span v-else>{{ $t('service.states.inactive') }}</span>
			</span>
			<br><br>
			<pre v-if='service.status !== null && !unsupported' class='log'>{{ service.status }}</pre>
		</CCard>
		<AptConfig v-if='serviceName === "unattended-upgrades" && $store.getters["features/isEnabled"]("unattendedUpgrades")' />
		<GatewayUserPassword v-if='serviceName === "ssh" && $store.getters["features/isEnabled"]("gatewayPass")' />
		<SystemdJournaldConfig v-if='serviceName === "systemd-journald" && $store.getters["features/isEnabled"]("systemdJournal")' />
	</div>
</template>

<script lang='ts'>
import {Component, Prop, Vue, Watch} from 'vue-property-decorator';
import {CButton, CCard} from '@coreui/vue/src';
import AptConfig from '@/components/Gateway/AptConfig.vue';
import GatewayUserPassword from '@/components/Gateway/GatewayUserPassword.vue';
import SystemdJournaldConfig from '@/components/Gateway/SystemdJournaldConfig.vue';

import AptService from '@/services/AptService';
import ServiceService from '@/services/ServiceService';
import {ErrorResponse} from '@/types';

import {AxiosError} from 'axios';
import {NavigationGuardNext, Route} from 'vue-router';
import {MetaInfo} from 'vue-meta';

const whitelisted = [
	'apcupsd',
	'iqrf-gateway-controller',
	'iqrf-gateway-daemon',
	'iqrf-gateway-translator',
	'ssh',
	'tempgw',
	'unattended-upgrades',
	'systemd-journald',
];

const features = {
	'apcupsd': 'apcupsd',
	'iqrf-gateway-controller': 'iqrfGatewayController',
	'iqrf-gateway-translator': 'iqrfGatewayTranslator',
	'ssh': 'ssh',
	'tempgw': 'iTemp',
	'unattended-upgrades': 'unattendedUpgrades',
	'systemd-journald': 'systemdJournal',
};

interface IService {
	active: boolean
	enabled: boolean
	status: string|null
}

@Component({
	components: {
		AptConfig,
		CButton,
		CCard,
		GatewayUserPassword,
		SystemdJournaldConfig,
	},
	beforeRouteEnter(_to: Route, from: Route, next: NavigationGuardNext): void {
		next((vm: Vue) => {
			const feature = features[vm.$props.serviceName];
			if (feature !== undefined &&
					!vm.$store.getters['features/isEnabled'](feature)) {
				vm.$toast.error(
					vm.$t(`service.${vm.$props.serviceName}.messages.disabled`).toString()
				);
				vm.$router.push(from.path);
			}
		});
	},
	metaInfo(): MetaInfo {
		return {
			title: (this as unknown as ServiceControl).pageTitle
		};
	}
})

/**
 * Gateway service control component
 */
export default class ServiceControl extends Vue {

	/**
	 * @var {boolean} missing Indicates that a service is missing
	 */
	private missing = false;

	/**
	 * @var {boolean} unknown Indicates that status of a service could not be retrieved
	 */
	private unknown = false;

	/**
	 * @var {boolean} unsupported Indicates that a service is not supported by the gateway
	 */
	private unsupported = false;

	/**
	 * @var {IService} service Service auxiliary data
	 */
	private service: IService = {
		active: false,
		enabled: false,
		status: null
	};

	/**
	 * @var {string} title Page and component card title, changes with service
	 */
	private title = '';

	/**
	 * @property {string} serviceName Name of service
	 */
	@Prop({required: true}) serviceName!: string;

	/**
	 * Computes page title depending on the service
	 * @returns {string} Page title
	 */
	get pageTitle(): string {
		const title = whitelisted.includes(this.serviceName) ?
			'service.' + this.serviceName + '.title' :
			'service.unsupported.title';
		return this.title = this.$t(title).toString();
	}

	/**
	 * Service name watcher for status retrieval
	 */
	@Watch('serviceName')
	getServiceStatus(): void {
		this.$store.commit('spinner/SHOW');
		if (this.serviceSupported()) {
			this.getStatus();
		}
	}

	/**
	 * Vue lifecycle hook created
	 */
	created(): void {
		this.$store.commit('spinner/SHOW');
		if (this.serviceSupported()) {
			this.getStatus();
		}
	}

	/**
	 * Write APT configuration
	 */
	private setUnattendedUpgrades(enable: boolean): void {
		AptService.setUnattendedUpgrades(enable)
			.then(() => this.handleSuccess(enable ? 'enable' : 'disable'))
			.catch(this.handleError);
	}

	/**
	 * Attempts to enable the service
	 */
	private enable(): void {
		this.$store.commit('spinner/SHOW');
		ServiceService.enable(this.serviceName)
			.then(() => {
				if (this.serviceName === 'unattended-upgrades') {
					this.setUnattendedUpgrades(true);
				} else {
					this.handleSuccess('enable');
				}
			})
			.catch(this.handleError);
	}

	/**
	 * Attempts to disable the service
	 */
	private disable(): void {
		this.$store.commit('spinner/SHOW');
		ServiceService.disable(this.serviceName)
			.then(() => {
				if (this.serviceName === 'unattended-upgrades') {
					this.setUnattendedUpgrades(false);
				} else {
					this.handleSuccess('disable');
				}
			})
			.catch(this.handleError);
	}

	/**
	 * Checks if requested service on the whitelist
	 * @returns {boolean} true if service is supported
	 * @returns {boolean} false if service is not supported
	 */
	private serviceSupported(): boolean {
		if (!whitelisted.includes(this.serviceName)) {
			this.unsupported = true;
			this.$store.commit('spinner/HIDE');
			this.$toast.error(
				this.$t('service.errors.unsupportedService').toString()
			);
			return false;
		}
		return true;
	}

	/**
	 * Attempts to retrieve status of the service
	 */
	private getStatus(): Promise<void> {
		return ServiceService.getStatus(this.serviceName)
			.then((status) => {
				this.service = status;
				this.missing = false;
				this.unknown = false;
				this.unsupported = false;
				this.$store.commit('spinner/HIDE');
			})
			.catch((error: AxiosError) => {
				this.handleError(error);
			});
	}

	/**
	 * Handles REST API error responses
	 */
	private handleError(error: AxiosError): void {
		this.$store.commit('spinner/HIDE');
		const response = error.response;
		if (response === undefined) {
			this.unknown = true;
			this.service.status = null;
			this.$toast.error(this.$t('service.errors.processTimeout').toString());
			return;
		}
		if (response.status === 404) {
			this.missing = true;
			this.$toast.error(this.$t('service.errors.missingService').toString());
		}
		if (response.status === 500 &&
			(response.data as ErrorResponse).message === 'Unsupported init system') {
			this.unsupported = false;
			this.$toast.error(this.$t('service.errors.unsupportedInit').toString());
		}
	}

	/**
	 * Handles successful REST API responses
	 */
	private handleSuccess(action: string): void {
		this.getStatus();
		this.$toast.success(
			this.$t(`service.${this.serviceName}.messages.${action}`).toString()
		);
	}

	/**
	 * Attempts to refresh status of the service
	 */
	private refreshStatus(): void {
		this.$store.commit('spinner/SHOW');
		this.getStatus()
			.then(() => this.$toast.success(this.$t('service.messages.refreshSuccess').toString()));
	}

	/**
	 * Attempts to restart the service
	 */
	private restart(): void {
		this.$store.commit('spinner/SHOW');
		ServiceService.restart(this.serviceName)
			.then(() => (this.handleSuccess('restart')))
			.catch(this.handleError);
	}

	/**
	 * Attempts to start the service
	 */
	private start(): void {
		this.$store.commit('spinner/SHOW');
		ServiceService.start(this.serviceName)
			.then(() => (this.handleSuccess('start')))
			.catch(this.handleError);
	}

	/**
	 * Attempts to stop the service
	 */
	private stop(): void {
		this.$store.commit('spinner/SHOW');
		ServiceService.stop(this.serviceName)
			.then(() => (this.handleSuccess('stop')))
			.catch(this.handleError);
	}
}
</script>
