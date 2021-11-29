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
		<h1>{{ $t('cloud.hexio.form.title') }}</h1>
		<CCard>
			<CCardBody>
				<ValidationObserver v-slot='{invalid}'>
					<CForm>
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
								required: "forms.errors.requestTopic"
							}'
						>
							<CInput
								v-model='config.topicRequest'
								:label='$t("forms.fields.requestTopic")'
								:is-valid='touched ? valid : null'
								:invalid-feedback='$t(errors[0])'
							/>
						</ValidationProvider>
						<ValidationProvider
							v-slot='{errors, touched, valid}'
							rules='required'
							:custom-messages='{
								required: "forms.errors.responseTopic"
							}'
						>
							<CInput
								v-model='config.topicResponse'
								:label='$t("forms.fields.responseTopic")'
								:is-valid='touched ? valid : null'
								:invalid-feedback='$t(errors[0])'
							/>
						</ValidationProvider>
						<ValidationProvider
							v-slot='{errors, touched, valid}'
							rules='required'
							:custom-messages='{
								required: "forms.errors.username"
							}'
						>
							<CInput
								v-model='config.username'
								:label='$t("forms.fields.username")'
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

import {daemonErrorToast, extendedErrorToast} from '../../helpers/errorToast';
import {required} from 'vee-validate/dist/rules';
import CloudService from '../../services/CloudService';
import ServiceService from '../../services/ServiceService';

import {AxiosError} from 'axios';
import {IHexioCloud} from '../../interfaces/clouds';

@Component({
	components: {
		CButton,
		CCard,
		CCardBody,
		CForm,
		CInput,
		FontAwesomeIcon,
		ValidationObserver,
		ValidationProvider
	},
	metaInfo: {
		title: 'cloud.hexio.form.title',
	},
})

/**
 * Hexio cloud mqtt connection configuration creator card
 */
export default class HexioCreator extends Vue {
	/**
	 * @constant {string} serviceName Hexio cloud service name
	 */
	private serviceName = 'hexio';

	/**
	 * @var {IHexioCloud} config Hexio cloud mqtt connection configuration
	 */
	private config: IHexioCloud = {
		broker: 'connect.hexio.cloud',
		clientId: '',
		topicRequest: 'Iqrf/DpaRequest',
		topicResponse: 'Iqrf/DpaResponse',
		username: '',
		password: ''
	};

	/**
	 * @var {string} visibility Form password field visibility type
	 */
	private visibility = 'password';
	
	/**
	 * Vue lifecycle hook created
	 */
	created(): void {
		extend('required', required);
	}

	/**
	 * Stores new hexio cloud connection configuration in the gateway filesystem
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
				extendedErrorToast(error, 'cloud.hexio.messages.saveFailed');
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
