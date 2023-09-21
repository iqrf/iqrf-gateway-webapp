<!--
Copyright 2017-2023 IQRF Tech s.r.o.
Copyright 2019-2023 MICRORISC s.r.o.

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
	<div class='box'>
		<div><strong>{{ $t('service.status') }}</strong></div>
		<div v-if='missing'>
			{{ $t('service.states.missing') }}
		</div>
		<div v-if='unsupported'>
			{{ $t('service.states.unsupported') }}
		</div>
		<div v-if='serviceStatus !== null'>
			{{ $t(`states.${serviceStatus.enabled ? 'enabled' : 'disabled'}`) }},
			{{ $t(`service.states.${serviceStatus.active ? 'active' : 'inactive'}`) }}
		</div>
		<div v-if='serviceStatus !== null' class='text-end'>
			<v-btn
				v-if='!serviceStatus.enabled'
				class='mr-1'
				color='success'
				small
				@click='enable'
			>
				{{ $t('service.actions.enable') }}
			</v-btn>
			<v-btn
				v-if='serviceStatus.enabled'
				class='mr-1'
				color='error'
				small
				@click='disable'
			>
				{{ $t('service.actions.disable') }}
			</v-btn>
			<v-btn
				color='primary'
				small
				@click='restart'
			>
				{{ $t('service.actions.restart') }}
			</v-btn>
		</div>
	</div>
</template>

<script lang='ts'>
import {ServiceService} from '@iqrf/iqrf-gateway-webapp-client/services';
import {ServiceStatus} from '@iqrf/iqrf-gateway-webapp-client/types';
import {AxiosError} from 'axios';
import {Component, Prop, Vue} from 'vue-property-decorator';

import {ErrorResponse} from '@/types';
import {useApiClient} from '@/services/ApiClient';

/**
 * Service control component
 */
@Component
export default class ServiceControl extends Vue {

	/**
	 * @property {string} serviceName Service name
	 */
	@Prop({required: true}) serviceName!: string;

	/**
	 * @var {boolean} missing Indicates whether the service is not installed
	 */
	private missing = false;

	/**
	 * @var {boolean} unsupported Indicates whether the service is supported
	 */
	private unsupported = false;

	/**
	 * @var {ServiceStatus|null} serviceStatus Service status object
	 */
	private serviceStatus: ServiceStatus|null = null;

	/**
	 * @property {ServiceService} service Service service
   */
	private service: ServiceService = useApiClient().getServiceService();

	/**
	 * Retrieves service status
	 */
	mounted(): void {
		this.getStatus();
	}

	/**
	 * Enables the service
	 */
	private enable(): void {
		this.$store.commit('spinner/SHOW');
		this.service.enable(this.serviceName)
			.then(() => {
				this.getStatus();
				this.$toast.success(
					this.$t(`service.${this.serviceName}.messages.enable`)
						.toString()
				);
			})
			.catch(this.handleError);
	}

	/**
	 * Disables the service
	 */
	private disable(): void {
		this.$store.commit('spinner/SHOW');
		this.service.disable(this.serviceName)
			.then(() => {
				this.getStatus();
				this.$toast.success(
					this.$t(`service.${this.serviceName}.messages.disable`)
						.toString()
				);
			})
			.catch(this.handleError);
	}

	/**
	 * Restarts the service
	 */
	private restart(): void {
		this.$store.commit('spinner/SHOW');
		this.service.restart(this.serviceName)
			.then(() => {
				this.getStatus();
				this.$toast.success(
					this.$t(`service.${this.serviceName}.messages.restart`)
						.toString()
				);
			})
			.catch(this.handleError);
	}

	/**
	 * Retrieves status of the service
	 */
	private getStatus(): void {
		if (!this.$store.getters['spinner/isEnabled']) {
			this.$store.commit('spinner/SHOW');
		}
		this.service.getStatus(this.serviceName)
			.then((status: ServiceStatus) => {
				this.serviceStatus = status;
				this.unsupported = false;
				this.$store.commit('spinner/HIDE');
			})
			.catch(this.handleError);
	}

	/**
	 * Handles REST API error responses
	 */
	private handleError(error: AxiosError): void {
		this.$store.commit('spinner/HIDE');
		this.serviceStatus = null;
		const response = error.response;
		if (response === undefined) {
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

}
</script>
