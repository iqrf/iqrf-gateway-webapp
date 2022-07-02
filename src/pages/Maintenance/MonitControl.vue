<!--
Copyright 2017-2022 IQRF Tech s.r.o.
Copyright 2019-2022 MICRORISC s.r.o.

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
		<h1>{{ $t('maintenance.monit.title') }}</h1>
		<CCard body-wrapper>
			<div class='box'>
				<div>
					<b>{{ $t('service.status') }}</b>
				</div>
				<div v-if='missing'>
					{{ $t('service.states.missing') }}
				</div>
				<div v-if='unsupported'>
					{{ $t('service.states.unsupported') }}
				</div>
				<div v-if='service !== null'>
					{{ $t('states.' + (service.enabled ? 'enabled' : 'disabled')) }},
					{{ $t('service.states.' + (service.active ? 'running' : 'stopped')) }}
				</div>
				<div v-if='service !== null'>
					<CButton
						v-if='!service.enabled'
						color='success'
						size='sm'
						@click='enable()'
					>
						{{ $t('service.actions.enable') }}
					</CButton> <CButton
						v-if='service.enabled'
						color='danger'
						size='sm'
						@click='disable()'
					>
						{{ $t('service.actions.disable') }}
					</CButton> <CButton
						color='primary'
						size='sm'
						@click='restart()'
					>
						{{ $t('service.actions.restart') }}
					</CButton>
				</div>
			</div>
			<MonitForm />
		</CCard>
	</div>
</template>

<script lang='ts'>
import {Component, Vue} from 'vue-property-decorator';
import {CButton, CCard} from '@coreui/vue/src';
import MonitForm from '@/components/Maintenance/MonitForm.vue';

import ServiceService from '@/services/ServiceService';
import {monitErrorToast} from '@/helpers/errorToast';

import {AxiosError} from 'axios';
import {NavigationGuardNext, Route} from 'vue-router';
import {ServiceStatus} from '@/services/ServiceService';

@Component({
	components: {
		CButton,
		CCard,
		MonitForm,
	},
	beforeRouteEnter(to: Route, from: Route, next: NavigationGuardNext): void {
		next((vm: Vue) => {
			if (!vm.$store.getters['features/isEnabled']('monit')) {
				vm.$toast.error(vm.$t('service.monit.messages.disabled').toString());
				vm.$router.push(from.path);
			}
		});
	},
	metaInfo: {
		title: 'maintenance.monit.title',
	},
})

/**
 * Monit service and configuration component
 */
export default class MonitControl extends Vue {

	/**
	 * @constant {string} serviceName Monit service name
	 */
	private serviceName = 'monit';

	/**
	 * @var {boolean} missing Indicates that Mender service is not installed
	 */
	private missing = false;

	/**
	 * @var {boolean} unsupported Indicates that Mender service is unsupported
	 */
	private unsupported = false;

	/**
	 * @var {ServiceStatus|null} service Mender service status object
	 */
	private service: ServiceStatus|null = null;

	/**
	 * Retrieves status of Monit service
	 */
	mounted(): void {
		this.getStatus();
	}

	/**
	 * Retrieves status of the Monit service
	 */
	private getStatus(): Promise<void> {
		if (!this.$store.getters['spinner/isEnabled']) {
			this.$store.commit('spinner/SHOW');
		}
		return ServiceService.getStatus(this.serviceName)
			.then((status: ServiceStatus) => {
				this.service = status;
				this.unsupported = false;
				this.$store.commit('spinner/HIDE');
			})
			.catch((error: AxiosError) => monitErrorToast(error, 'service.messages.statusFailed'));
	}

	/**
	 * Enables the Monit service
	 */
	private enable(): void {
		this.$store.commit('spinner/SHOW');
		ServiceService.enable(this.serviceName)
			.then(() => this.serviceSuccess('service.' + this.serviceName + '.messages.enable'))
			.catch((error: AxiosError) => monitErrorToast(error, 'service.messages.enableFailed'));
	}

	/**
	 * Disables the Monit service
	 */
	private disable(): void {
		this.$store.commit('spinner/SHOW');
		ServiceService.disable(this.serviceName)
			.then(() => this.serviceSuccess('service.' + this.serviceName + '.messages.disable'))
			.catch((error: AxiosError) => monitErrorToast(error, 'service.messages.disableFailed'));
	}

	/**
	 * Restarts the Monit service
	 */
	private restart(): void {
		this.$store.commit('spinner/SHOW');
		ServiceService.restart(this.serviceName)
			.then(() => this.serviceSuccess('service.' + this.serviceName + '.messages.restart'))
			.catch((error: AxiosError) => monitErrorToast(error, 'service.messages.restartFailed'));
	}

	/**
	 * Handles successful Service operations
	 * @param {string} message Toast message
	 */
	private serviceSuccess(message: string): void {
		this.getStatus().then(() => {
			this.$toast.success(
				this.$t(message).toString()
			);
		});
	}

}
</script>
