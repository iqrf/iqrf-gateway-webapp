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
		<h1>{{ $t('cloud.msAzure.form.title') }}</h1>
		<v-card>
			<v-card-title>
				<v-item-group>
					<v-btn
						color='primary'
						small
						href='https://github.com/iqrfsdk/iot-starter-kit/blob/master/install/pdf/iqrf-part3b.pdf'
						target='_blank'
					>
						<v-icon small>
							mdi-file-document
						</v-icon>
						{{ $t('cloud.guides.pdf') }}
					</v-btn> <v-btn
						color='error'
						small
						href='https://youtu.be/SIBoTrYwR2g'
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
					<v-form>
						<ValidationProvider
							v-slot='{errors, touched, valid}'
							rules='required'
							:custom-messages='{
								required: $t("cloud.msAzure.errors.connectionString"),
							}'
						>
							<v-text-field
								v-model='config.connectionString'
								:label='$t("cloud.msAzure.form.connectionString")'
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
					</v-form>
				</ValidationObserver>
			</v-card-text>
		</v-card>
	</div>
</template>

<script lang='ts'>
import {Client} from '@iqrf/iqrf-gateway-webapp-client';
import {AzureIotHubConfig} from '@iqrf/iqrf-gateway-webapp-client/types/Cloud';
import {AxiosError} from 'axios';
import {extend, ValidationObserver, ValidationProvider} from 'vee-validate';
import {required} from 'vee-validate/dist/rules';
import {Component, Vue} from 'vue-property-decorator';

import {daemonErrorToast, extendedErrorToast} from '@/helpers/errorToast';
import {useApiClient} from '@/services/ApiClient';

@Component({
	components: {
		ValidationObserver,
		ValidationProvider
	},
	metaInfo: {
		title: 'cloud.msAzure.form.title',
	},
})

/**
 * Azure cloud mqtt connection configuration creator card
 */
export default class AzureCreator extends Vue {

	/**
	 * @property {AzureIotHubConfig} config Azure IoT Hub MQTT connection configuration
   * @private
   */
	private config: AzureIotHubConfig = {
		connectionString: ''
	};

	/**
	 * @property {Client} apiClient IQRF Gateway Webapp API client
   * @private
   */
	private apiClient: Client = useApiClient();

	/**
	 * Vue lifecycle hook created
	 */
	created(): void {
		extend('required', required);
	}

	/**
	 * Stores new Azure cloud connection configuration in the gateway filesystem
	 * @param {boolean} restart Restart daemon on save?
	 */
	private save(restart: boolean): void {
		this.$store.commit('spinner/SHOW');
		this.apiClient.getCloudServices().getAzureService().createMqttInstance(this.config)
			.then(async () => {
				if (restart) {
					await this.apiClient.getServiceService().restart('iqrf-gateway-daemon')
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
				extendedErrorToast(error, 'cloud.msAzure.messages.saveFailed');
			});
	}

}
</script>
