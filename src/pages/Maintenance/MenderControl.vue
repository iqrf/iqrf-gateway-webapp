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
		<h1>{{ $t('maintenance.mender.service.pageTitle') }}</h1>
		<v-card>
			<v-card-text>
				<div class='box'>
					<div>
						<strong>{{ $t('service.status') }}</strong>
					</div>
					<div v-if='missing'>
						{{ $t('service.states.missing') }}
					</div>
					<div v-if='unsupported'>
						{{ $t('service.states.unsupported') }}
					</div>
					<div v-if='service !== null'>
						{{ $t(`states.${service.enabled ? 'enabled' : 'disabled'}`) }},
						{{ $t(`service.states.${service.active ? '' : 'in'}active`) }}
					</div>
					<div v-if='service !== null'>
						<v-btn
							v-if='!service.enabled'
							color='success'
							small
							@click='enable()'
						>
							{{ $t('service.actions.enable') }}
						</v-btn> <v-btn
							v-if='service.enabled'
							color='error'
							small
							@click='disable()'
						>
							{{ $t('service.actions.disable') }}
						</v-btn> <v-btn
							color='primary'
							small
							@click='restart()'
						>
							{{ $t('service.actions.restart') }}
						</v-btn>
					</div>
				</div>
				<MenderServiceForm />
			</v-card-text>
		</v-card>
	</div>
</template>

<script lang='ts'>
import {Component, Vue} from 'vue-property-decorator';
import MenderServiceForm from '@/components/Maintenance/MenderServiceForm.vue';

import ServiceService, {ServiceStatus} from '@/services/ServiceService';
import {menderErrorToast} from '@/helpers/errorToast';

import {AxiosError} from 'axios';
import {NavigationGuardNext, Route} from 'vue-router';

@Component({
	components: {
		MenderServiceForm,
	},
	beforeRouteEnter(_to: Route, from: Route, next: NavigationGuardNext): void {
		next((vm: Vue) => {
			if (!vm.$store.getters['features/isEnabled']('mender')) {
				vm.$toast.error(vm.$t('service.mender-client.messages.disabled').toString());
				vm.$router.push(from.path);
			}
		});
	},
	metaInfo: {
		title: 'maintenance.mender.service.pageTitle',
	},
})

/**
 * Mender control component
 */
export default class MenderControl extends Vue {

	/**
	 * @constant {string} serviceName Mender service name
	 */
	private serviceName = 'mender-client';

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
	 * Vue lifecycle hook mounted
	 */
	mounted(): void {
		this.getStatus();
	}

	/**
	 * Retrieves status of the Mender service
	 */
	private getStatus(): Promise<void> {
		return ServiceService.getStatus(this.serviceName)
			.then((status: ServiceStatus) => {
				this.service = status;
				this.unsupported = false;
				this.$store.commit('spinner/HIDE');
			})
			.catch((error: AxiosError) => menderErrorToast(error, 'service.messages.statusFailed'));
	}

	/**
	 * Enables the Mender service
	 */
	private enable(): void {
		this.$store.commit('spinner/SHOW');
		ServiceService.enable(this.serviceName)
			.then(() => this.serviceSuccess('service.mender-client.messages.enable'))
			.catch((error: AxiosError) => menderErrorToast(error, 'service.messages.enableFailed'));
	}

	/**
	 * Disables the Mender service
	 */
	private disable(): void {
		this.$store.commit('spinner/SHOW');
		ServiceService.disable(this.serviceName)
			.then(() => this.serviceSuccess('service.mender-client.messages.disable'))
			.catch((error: AxiosError) => menderErrorToast(error, 'service.messages.disableFailed'));
	}

	/**
	 * Restarts the Mender service
	 */
	private restart(): void {
		this.$store.commit('spinner/SHOW');
		ServiceService.restart(this.serviceName)
			.then(() => this.serviceSuccess('service.mender-client.messages.restart'))
			.catch((error: AxiosError) => menderErrorToast(error, 'service.messages.restartFailed'));
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
