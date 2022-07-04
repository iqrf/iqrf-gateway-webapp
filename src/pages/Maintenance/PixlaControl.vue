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
		<header class='d-flex'>
			<h1 class='mr-auto'>
				{{ $t('maintenance.pixla.title') }}
			</h1>
			<div v-if='!missing && !unsupported'>
				<v-btn
					color='primary'
					href='https://www.pixla.online/'
					target='_blank'
				>
					{{ $t('maintenance.pixla.dashboard') }}
				</v-btn>
			</div>
		</header>
		<v-card>
			<v-card-text>
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
					{{ $t(`service.states.${service.active ? 'active' : 'inactive'}`) }}
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
				<PixlaForm />
			</v-card-text>
		</v-card>
	</div>
</template>

<script lang='ts'>
import {Component, Vue} from 'vue-property-decorator';
import PixlaForm from '@/components/Maintenance/PixlaForm.vue';

import {pixlaErrorToast} from '@/helpers/errorToast';

import ServiceService, {ServiceStatus} from '@/services/ServiceService';

import {AxiosError} from 'axios';
import {NavigationGuardNext, Route} from 'vue-router';

@Component({
	components: {
		PixlaForm,
	},
	beforeRouteEnter(to: Route, from: Route, next: NavigationGuardNext): void {
		next((vm: Vue) => {
			if (!vm.$store.getters['features/isEnabled']('pixla')) {
				vm.$toast.error(vm.$t('service.gwman-client.messages.disabled').toString());
				vm.$router.push(from.path);
			}
		});
	},
	metaInfo: {
		title: 'maintenance.pixla.title',
	},
})

/**
 * Pixla maintenance service manager card
 */
export default class PixlaControl extends Vue {
	/**
	 * @constant {string} serviceName Pixla service name
	 */
	private serviceName = 'gwman-client';

	/**
	 * @var {boolean} missing Indicates whether the pixla service is not installed
	 */
	private missing = false;

	/**
	 * @var {boolean} unsupported Indicates whether the pixla service is supported
	 */
	private unsupported = false;

	/**
	 * @var {ServiceStatus|null} service Pixla service status object
	 */
	private service: ServiceStatus|null = null;

	/**
	 * Retrieves service status
	 */
	mounted(): void {
		this.getStatus();
	}

	/**
	 * Enables the Pixla service
	 */
	private enable(): void {
		this.$store.commit('spinner/SHOW');
		ServiceService.enable(this.serviceName)
			.then(() => {
				this.getStatus();
				this.$toast.success(
					this.$t('service.gwman-client.messages.enable')
						.toString()
				);
			})
			.catch((error: AxiosError) => pixlaErrorToast(error, 'service.messages.enableFailed'));
	}

	/**
	 * Disables the Pixla service
	 */
	private disable(): void {
		this.$store.commit('spinner/SHOW');
		ServiceService.disable(this.serviceName)
			.then(() => {
				this.getStatus();
				this.$toast.success(
					this.$t('service.gwman-client.messages.disable')
						.toString()
				);
			})
			.catch((error: AxiosError) => pixlaErrorToast(error, 'service.messages.disableFailed'));
	}

	/**
	 * Restarts the Pixla service
	 */
	private restart(): void {
		this.$store.commit('spinner/SHOW');
		ServiceService.restart(this.serviceName)
			.then(() => {
				this.getStatus();
				this.$toast.success(
					this.$t('service.gwman-client.messages.restart')
						.toString()
				);
			})
			.catch((error: AxiosError) => pixlaErrorToast(error, 'service.messages.restartFailed'));
	}

	/**
	 * Retrieves status of the Pixla service
	 */
	private getStatus(): void {
		if (!this.$store.getters['spinner/isEnabled']) {
			this.$store.commit('spinner/SHOW');
		}
		ServiceService.getStatus(this.serviceName)
			.then((status: ServiceStatus) => {
				this.service = status;
				this.unsupported = false;
				this.$store.commit('spinner/HIDE');
			})
			.catch((error: AxiosError) => pixlaErrorToast(error, 'service.messages.statusFailed'));
	}

}
</script>
