<!--
Copyright 2017-2024 IQRF Tech s.r.o.
Copyright 2019-2024 MICRORISC s.r.o.

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
		<h1>{{ $t('cloud.ibmCloud.form.title') }}</h1>
		<v-card>
			<v-card-title>
				<v-item-group>
					<v-btn
						color='primary'
						small
						href='https://github.com/iqrfsdk/iot-starter-kit/blob/master/install/pdf/iqrf-part3c.pdf'
						target='_blank'
					>
						<v-icon small>
							mdi-file-document
						</v-icon>
						{{ $t('cloud.guides.pdf') }}
					</v-btn> <v-btn
						color='error'
						small
						href='https://youtu.be/xoAReOyrkZ4'
						target='_blank'
					>
						<v-icon small>
							mdi-youtube
						</v-icon>
						{{ $t('cloud.guides.video') }}
					</v-btn>
				</v-item-group>
			</v-card-title>
			<v-card-text>
				<ValidationObserver v-slot='{invalid}'>
					<form>
						<ValidationProvider
							v-slot='{errors, touched, valid}'
							rules='required'
							:custom-messages='{
								required: $t("cloud.ibmCloud.errors.organizationId"),
							}'
						>
							<v-text-field
								v-model='config.organizationId'
								:label='$t("cloud.ibmCloud.form.organizationId")'
								:success='touched ? valid : null'
								:error-messages='errors'
							/>
						</ValidationProvider>
						<ValidationProvider
							v-slot='{errors, touched, valid}'
							rules='required'
							:custom-messages='{
								required: $t("cloud.ibmCloud.errors.deviceType"),
							}'
						>
							<v-text-field
								v-model='config.deviceType'
								:label='$t("cloud.ibmCloud.form.deviceType")'
								:success='touched ? valid : null'
								:error-messages='errors'
							/>
						</ValidationProvider>
						<ValidationProvider
							v-slot='{errors, touched, valid}'
							rules='required'
							:custom-messages='{
								required: $t("cloud.ibmCloud.errors.deviceId"),
							}'
						>
							<v-text-field
								v-model='config.deviceId'
								:label='$t("cloud.ibmCloud.form.deviceId")'
								:success='touched ? valid : null'
								:error-messages='errors'
							/>
						</ValidationProvider>
						<ValidationProvider
							v-slot='{errors, touched, valid}'
							rules='required'
							:custom-messages='{
								required: $t("cloud.ibmCloud.errors.token"),
							}'
						>
							<v-text-field
								v-model='config.token'
								:label='$t("cloud.ibmCloud.form.token")'
								:success='touched ? valid : null'
								:error-messages='errors'
							/>
						</ValidationProvider>
						<ValidationProvider
							v-slot='{errors, touched, valid}'
							rules='required'
							:custom-messages='{
								required: $t("cloud.ibmCloud.errors.eventId"),
							}'
						>
							<v-text-field
								v-model='config.eventId'
								:label='$t("cloud.ibmCloud.form.eventId")'
								:success='touched ? valid : null'
								:error-messages='errors'
							/>
						</ValidationProvider>
						<v-btn
							class='mr-1'
							color='primary'
							:disabled='invalid'
							@click.prevent='save(false)'
						>
							{{ $t('forms.save') }}
						</v-btn>
						<v-btn
							color='primary'
							:disabled='invalid'
							@click.prevent='save(true)'
						>
							{{ $t('forms.saveRestart') }}
						</v-btn>
					</form>
				</ValidationObserver>
			</v-card-text>
		</v-card>
	</div>
</template>

<script lang='ts'>
import {Component, Vue} from 'vue-property-decorator';
import {extend, ValidationObserver, ValidationProvider} from 'vee-validate';

import {daemonErrorToast, extendedErrorToast} from '@/helpers/errorToast';
import {required} from 'vee-validate/dist/rules';

import {AxiosError} from 'axios';
import {useApiClient} from '@/services/ApiClient';
import type {
	IbmCloudConfig
} from '@iqrf/iqrf-gateway-webapp-client/types/Cloud/Ibm';

@Component({
	components: {
		ValidationObserver,
		ValidationProvider
	},
	metaInfo: {
		title: 'cloud.ibmCloud.form.title',
	},
})

/**
 * Ibm cloud mqtt connection configuration creator card
 */
export default class IbmCreator extends Vue {

	/**
	 * @var {IIbmCloud} config Ibm cloud connection configuration
	 */
	private config: IbmCloudConfig = {
		organizationId: '',
		deviceType: '',
		deviceId: '',
		token: '',
		eventId: 'iqrf'
	};

	/**
	 * Vue lifecycle hook created
	 */
	created(): void {
		extend('required', required);
	}

	/**
	 * Stores new ibm cloud connection configuration in the gateway filesystem
	 * @param {boolean} restart Restart daemon on save?
	 */
	private save(restart: boolean): void {
		this.$store.commit('spinner/SHOW');
		useApiClient().getCloudServices().getIbmService().createMqttInstance(this.config)
			.then(async () => {
				if (restart) {
					await useApiClient().getServiceService().restart('iqrf-gateway-daemon')
						.then(() => {
							this.$toast.success(
								this.$t('service.iqrf-gateway-daemon.messages.restart')
									.toString()
							);
						})
						.catch((error: AxiosError) => daemonErrorToast(error, 'service.messages.restartFailed'));
				}
				this.$store.commit('spinner/HIDE');
				this.$toast.success(this.$t('cloud.messages.success').toString());
			})
			.catch((error: AxiosError) => {
				extendedErrorToast(error, 'cloud.ibmCloud.messages.saveFailed');
			});
	}

}
</script>
