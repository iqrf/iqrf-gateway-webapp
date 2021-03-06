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
		<h1>{{ $t('cloud.intelimentsInteliGlue.form.title') }}</h1>
		<CCard>
			<CCardBody>
				<ValidationObserver v-slot='{invalid}'>
					<CForm>
						<ValidationProvider
							v-slot='{errors, touched, valid}'
							rules='required'
							:custom-messages='{
								required: "cloud.intelimentsInteliGlue.errors.rootTopic"
							}'
						>
							<CInput
								v-model='config.rootTopic'
								:label='$t("cloud.intelimentsInteliGlue.form.rootTopic")'
								:is-valid='touched ? valid : null'
								:invalid-feedback='$t(errors[0])'
							/>
						</ValidationProvider>
						<ValidationProvider
							v-slot='{errors, touched, valid}'
							rules='required|integer|between:0,65535'
							:custom-messages='{
								between: "cloud.intelimentsInteliGlue.errors.assignedPortRange",
								integer: "cloud.intelimentsInteliGlue.errors.assignedPortRange",
								required: "cloud.intelimentsInteliGlue.errors.assignedPort"
							}'
						>
							<CInput
								v-model.number='config.assignedPort'
								type='number'
								min='0'
								max='65535'
								:label='$t("cloud.intelimentsInteliGlue.form.assignedPort")'
								:is-valid='touched ? valid : null'
								:invalid-feedback='$t(errors[0])'
							/>
						</ValidationProvider>
						<ValidationProvider
							v-slot='{errors, touched, valid}'
							rules='required'
							:custom-messages='{
								required: "forms.errors.clientId"
							}'
						>
							<CInput
								v-model='config.clientId'
								:label='$t("forms.fields.clientId")'
								:is-valid='touched ? valid : null'
								:invalid-feedback='$t(errors[0])'
							/>
						</ValidationProvider>
						<ValidationProvider
							v-slot='{errors, touched, valid}'
							rules='required'
							:custom-messages='{
								required: "forms.errors.password"
							}'
						>
							<CInput
								v-model='config.password'
								:label='$t("forms.fields.password")'
								:is-valid='touched ? valid : null'
								:invalid-feedback='$t(errors[0])'
								:type='visibility'
							>
								<template #append-content>
									<span @click='changeVisibility'>
										<FontAwesomeIcon
											:icon='(visibility === "password" ? ["far", "eye"] : ["far", "eye-slash"])'
										/>
									</span>
								</template>
							</CInput>
						</ValidationProvider>
						<CButton
							color='primary'
							:disabled='invalid'
							@click.prevent='save(false)'
						>
							{{ $t('forms.save') }}
						</CButton> <CButton
							color='secondary'
							:disabled='invalid'
							@click.prevent='save(true)'
						>
							{{ $t('forms.saveRestart') }}
						</CButton>
					</CForm>
				</ValidationObserver>
			</CCardBody>
		</CCard>
	</div>
</template>

<script lang='ts'>
import {Component, Vue} from 'vue-property-decorator';
import {CButton, CCard, CCardBody, CForm, CInput} from '@coreui/vue/src';
import {extend, ValidationObserver, ValidationProvider} from 'vee-validate';
import {FontAwesomeIcon} from '@fortawesome/vue-fontawesome';

import {between, integer, required} from 'vee-validate/dist/rules';
import {daemonErrorToast, extendedErrorToast} from '../../helpers/errorToast';
import CloudService from '../../services/CloudService';
import ServiceService from '../../services/ServiceService';

import {AxiosError} from 'axios';
import {IInteliGlueCloud} from '../../interfaces/clouds';

@Component({
	components: {
		CButton,
		CCard,
		CCardBody,
		CForm,
		CInput,
		FontAwesomeIcon,
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
	private serviceName = 'inteliGlue'

	/**
	 * @var {InteliGlueConfig} config InteliGlue cloud connection configuration
	 */
	private config: IInteliGlueCloud = {
		rootTopic: '',
		assignedPort: 1234,
		clientId: '',
		password: ''
	}

	/**
	 * @var {string} visibility Form password field visibility type
	 */
	private visibility = 'password'

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

	/**
	 * Changes password input field visibility
	 */
	private changeVisibility(): void {
		this.visibility = this.visibility === 'password' ? 'text': 'password';
	}
}
</script>
