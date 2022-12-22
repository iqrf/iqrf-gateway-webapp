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
		<h1 v-if='$route.path === "/config/daemon/messagings/udp/add"'>
			{{ $t('config.daemon.messagings.udp.add') }}
		</h1>
		<h1 v-else>
			{{ $t('config.daemon.messagings.udp.edit') }}
		</h1>
		<CCard>
			<CCardBody>
				<ValidationObserver v-slot='{invalid}'>
					<CForm @submit.prevent='saveConfig'>
						<ValidationProvider
							v-slot='{errors, touched, valid}'
							rules='required|instance'
							:custom-messages='{
								required: $t("config.daemon.messagings.udp.errors.instance"),
								instance: $t("config.daemon.messagings.instanceInvalid"),
							}'
						>
							<CInput
								v-model='configuration.instance'
								:label='$t("forms.fields.instanceName")'
								:is-valid='touched ? valid : null'
								:invalid-feedback='errors.join(", ")'
							/>
						</ValidationProvider>
						<ValidationProvider
							v-slot='{errors, touched, valid}'
							rules='required|between:1,65535'
							:custom-messages='{
								between: $t("config.daemon.messagings.udp.errors.RemotePort"),
								required: $t("config.daemon.messagings.udp.errors.RemotePort"),
							}'
						>
							<CInput
								v-model.number='configuration.RemotePort'
								:label='$t("config.daemon.messagings.udp.form.RemotePort")'
								:is-valid='touched ? valid : null'
								:invalid-feedback='errors.join(", ")'
								type='number'
								min='1'
								max='65535'
							/>
						</ValidationProvider>
						<ValidationProvider
							v-slot='{errors, touched, valid}'
							rules='required|between:1,65535'
							:custom-messages='{
								between: $t("config.daemon.messagings.udp.errors.LocalPort"),
								required: $t("config.daemon.messagings.udp.errors.LocalPort"),
							}'
						>
							<CInput
								v-model.number='configuration.LocalPort'
								:label='$t("config.daemon.messagings.udp.form.LocalPort")'
								:is-valid='touched ? valid : null'
								:invalid-feedback='errors.join(", ")'
								type='number'
								min='1'
								max='65535'
							/>
						</ValidationProvider>
						<CButton type='submit' color='primary' :disabled='invalid'>
							{{ submitButton }}
						</CButton>
					</CForm>
				</ValidationObserver>
			</CCardBody>
		</CCard>
	</div>
</template>

<script lang='ts'>
import {Component, Prop, Vue} from 'vue-property-decorator';
import {CButton, CCard, CCardBody, CCardHeader, CForm, CInput} from '@coreui/vue/src';
import {extend, ValidationObserver, ValidationProvider} from 'vee-validate';

import {between, required} from 'vee-validate/dist/rules';
import {extendedErrorToast} from '@/helpers/errorToast';
import {daemonInstanceName} from '@/helpers/validators';
import DaemonConfigurationService from '@/services/DaemonConfigurationService';

import {AxiosError, AxiosResponse} from 'axios';
import {IUdpInstance} from '@/interfaces/Config/Messaging';
import {MetaInfo} from 'vue-meta';

@Component({
	components: {
		CButton,
		CCard,
		CCardBody,
		CCardHeader,
		CForm,
		CInput,
		ValidationObserver,
		ValidationProvider,
	},
	metaInfo(): MetaInfo {
		return {
			title: (this as unknown as UdpMessagingForm).pageTitle
		};
	}
})

/**
 * Daemon UDP messaging component configuration form
 */
export default class UdpMessagingForm extends Vue {
	/**
	 * @constant {string} componentName UDP messaging component name
	 */
	private componentName = 'iqrf::UdpMessaging';

	/**
	 * @var {IUdpInstance} configuration UDP messaging component instance configuration
	 */
	private configuration: IUdpInstance = {
		component: '',
		instance: '',
		RemotePort: 55000,
		LocalPort: 55300
	};

	/**
	 * @property {string} instance UDP messaging component instance name
	 */
	@Prop({required: false, default: ''}) instance!: string;

	/**
	 * Computes page title depending on the action (add, edit)
	 * @returns {string} Page title
	 */
	get pageTitle(): string {
		return this.$route.path === '/config/daemon/messagings/udp/add' ?
			this.$t('config.daemon.messagings.udp.add').toString() : this.$t('config.daemon.messagings.udp.edit').toString();
	}

	/**
	 * Computes the text of form submit button depending on the action (add, edit)
	 * @returns {string} Button text
	 */
	get submitButton(): string {
		return this.$route.path === '/config/daemon/messagings/udp/add' ?
			this.$t('forms.add').toString() : this.$t('forms.edit').toString();
	}

	/**
	 * Initializes validation rules
	 */
	created(): void {
		extend('between', between);
		extend('required', required);
		extend('instance', daemonInstanceName);
	}

	/**
	 * Retrieves component instance configuration
	 */
	mounted(): void {
		if (this.instance !== '') {
			this.getConfig();
		}
	}

	/**
	 * Retrieves configuration of the UDP messaging component instance
	 */
	private getConfig(): void {
		this.$store.commit('spinner/SHOW');
		DaemonConfigurationService.getInstance(this.componentName, this.instance)
			.then((response: AxiosResponse) => {
				this.$store.commit('spinner/HIDE');
				this.configuration = response.data;
			})
			.catch((error: AxiosError) => {
				extendedErrorToast(error, 'config.daemon.messagings.udp.messages.fetchFailed', {instance: this.instance});
				this.$router.push('/config/daemon/messagings/udp');
			});
	}

	/**
	 * Saves new or updates existing configuration of UDP messaging component instance
	 */
	private saveConfig(): void {
		this.$store.commit('spinner/SHOW');
		if (this.instance !== '') {
			DaemonConfigurationService.updateInstance(this.componentName, this.instance, this.configuration)
				.then(() => this.successfulSave())
				.catch((error: AxiosError) => extendedErrorToast(error, 'config.daemon.messagings.udp.messages.editFailed', {instance: this.instance}));
		} else {
			DaemonConfigurationService.createInstance(this.componentName, this.configuration)
				.then(() => this.successfulSave())
				.catch((error: AxiosError) => extendedErrorToast(error, 'config.daemon.messagings.udp.messages.addFailed'));
		}
	}

	/**
	 * Handles successful REST API response
	 */
	private successfulSave(): void {
		this.$store.commit('spinner/HIDE');
		if (this.$route.path === '/config/daemon/messagings/udp/add') {
			this.$toast.success(
				this.$t('config.daemon.messagings.udp.messages.addSuccess', {instance: this.configuration.instance})
					.toString());
		} else {
			this.$toast.success(
				this.$t('config.daemon.messagings.udp.messages.editSuccess', {instance: this.configuration.instance})
					.toString()
			);
		}
		this.$router.push('/config/daemon/messagings/udp/');
	}
}
</script>
