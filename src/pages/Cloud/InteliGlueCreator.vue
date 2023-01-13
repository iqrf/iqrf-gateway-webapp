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
	<div>
		<h1>{{ $t('cloud.intelimentsInteliGlue.form.title') }}</h1>
		<v-card>
			<v-card-text>
				<ValidationObserver v-slot='{invalid}'>
					<form>
						<ValidationProvider
							v-slot='{errors, touched, valid}'
							rules='required'
							:custom-messages='{
								required: $t("cloud.intelimentsInteliGlue.errors.rootTopic"),
							}'
						>
							<v-text-field
								v-model='config.rootTopic'
								:label='$t("cloud.intelimentsInteliGlue.form.rootTopic")'
								:success='touched ? valid : null'
								:error-messages='errors'
							/>
						</ValidationProvider>
						<ValidationProvider
							v-slot='{errors, touched, valid}'
							rules='required|integer|between:0,65535'
							:custom-messages='{
								between: $t("cloud.intelimentsInteliGlue.errors.assignedPortRange"),
								integer: $t("cloud.intelimentsInteliGlue.errors.assignedPortRange"),
								required: $t("cloud.intelimentsInteliGlue.errors.assignedPort"),
							}'
						>
							<v-text-field
								v-model.number='config.assignedPort'
								type='number'
								min='0'
								max='65535'
								:label='$t("cloud.intelimentsInteliGlue.form.assignedPort")'
								:success='touched ? valid : null'
								:error-messages='errors'
							/>
						</ValidationProvider>
						<ValidationProvider
							v-slot='{errors, touched, valid}'
							rules='required'
							:custom-messages='{
								required: $t("forms.errors.clientId"),
							}'
						>
							<v-text-field
								v-model='config.clientId'
								:label='$t("forms.fields.clientId")'
								:success='touched ? valid : null'
								:error-messages='errors'
							/>
						</ValidationProvider>
						<ValidationProvider
							v-slot='{errors, touched, valid}'
							rules='required'
							:custom-messages='{
								required: $t("forms.errors.password"),
							}'
						>
							<PasswordInput
								v-model='config.password'
								:label='$t("forms.fields.password")'
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
import PasswordInput from '@/components/Core/PasswordInput.vue';

import {between, integer, required} from 'vee-validate/dist/rules';
import {daemonErrorToast, extendedErrorToast} from '@/helpers/errorToast';
import CloudService from '@/services/CloudService';
import ServiceService from '@/services/ServiceService';

import {AxiosError} from 'axios';
import {IInteliGlueCloud} from '@/interfaces/Clouds';

@Component({
	components: {
		PasswordInput,
		ValidationObserver,
		ValidationProvider,
	},
	metaInfo: {
		title: 'cloud.intelimentsInteliGlue.form.title',
	}
})

/**
 * InteliGlue inteliments cloud mqtt connection configuration creator card
 */
export default class InteliGlueCreator extends Vue {
	/**
	 * @constant {string} serviceName InteliGlue cloud service name
	 */
	private serviceName = 'inteliGlue';

	/**
	 * @var {InteliGlueConfig} config InteliGlue cloud connection configuration
	 */
	private config: IInteliGlueCloud = {
		rootTopic: '',
		assignedPort: 1234,
		clientId: '',
		password: ''
	};

	/**
	 * Vue lifecycle hook created
	 */
	created(): void {
		extend('between', between);
		extend('integer', integer);
		extend('required', required);
	}

	/**
	 * Stores new inteliglue cloud connection configuration in the gateway filesystem
	 * @param {boolean} restart Restart daemon on save?
	 */
	private save(restart: boolean): void {
		this.$store.commit('spinner/SHOW');
		CloudService.create(this.serviceName, this.config)
			.then(async () => {
				if (restart) {
					await ServiceService.restart('iqrf-gateway-daemon')
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
				extendedErrorToast(error, 'cloud.intelimentsInteliGlue.messages.saveFailed');
				return Promise.reject();
			});
	}
}
</script>
