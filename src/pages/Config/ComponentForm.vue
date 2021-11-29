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
		<h1 v-if='$route.path === "/config/daemon/component/add"'>
			{{ $t('config.daemon.components.add') }}
		</h1>
		<h1 v-else>
			{{ $t('config.daemon.components.edit') }}
		</h1>
		<CCard>
			<CCardBody>
				<ValidationObserver v-slot='{ invalid }'>
					<CForm @submit.prevent='saveComponent'>
						<ValidationProvider
							v-slot='{ errors, touched, valid }'
							rules='required'
							:custom-messages='{required: "config.daemon.components.errors.name"}'
						>
							<CInput
								v-model='configuration.name'
								:label='$t("config.daemon.components.form.name")'
								:is-valid='touched ? valid : null'
								:invalid-feedback='$t(errors[0])'
							/>
						</ValidationProvider>
						<CInput
							v-model='configuration.libraryPath'
							:label='$t("config.daemon.components.form.libraryPath")'
						/>
						<ValidationProvider
							v-slot='{ errors, touched, valid }'
							rules='required'
							:custom-messages='{required: "config.daemon.components.errors.libraryName"}'
						>
							<CInput
								v-model='configuration.libraryName'
								:label='$t("config.daemon.components.form.libraryName")'
								:is-valid='touched ? valid : null'
								:invalid-feedback='$t(errors[0])'
							/>
						</ValidationProvider>
						<CInputCheckbox
							:checked.sync='configuration.enabled'
							:label='$t("states.enabled")'
						/>
						<ValidationProvider
							v-slot='{ errors, touched, valid }'
							rules='integer|required'
							:custom-messages='{
								integer: "forms.errors.integer",
								required: "config.daemon.components.errors.startLevel"
							}'
						>
							<CInput
								v-model.number='configuration.startlevel'
								type='number'
								:label='$t("config.daemon.components.form.startLevel")'
								:is-valid='touched ? valid : null'
								:invalid-feedback='$t(errors[0])'
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
import {CButton, CCard, CCardBody, CCardHeader, CForm, CInput, CInputCheckbox} from '@coreui/vue/src';
import {extend, ValidationObserver, ValidationProvider} from 'vee-validate';
import {integer, required} from 'vee-validate/dist/rules';
import DaemonConfigurationService from '../../services/DaemonConfigurationService';
import FormErrorHandler from '../../helpers/FormErrorHandler';
import { MetaInfo } from 'vue-meta/types/vue-meta';
import { AxiosError, AxiosResponse } from 'axios';

interface ComponentFormConfig {
	name: string
	libraryPath: string
	libraryName: string
	enabled: boolean
	startlevel: number
}

@Component({
	components: {
		CButton,
		CCard,
		CCardBody,
		CCardHeader,
		CForm,
		CInput,
		CInputCheckbox,
		ValidationObserver,
		ValidationProvider,
	},
	metaInfo(): MetaInfo {
		return {
			title: (this as unknown as ComponentForm).pageTitle
		};
	}
})

/**
 * Component form card for Daemon component configuration
 */
export default class ComponentForm extends Vue {
	/**
	 * @var {ComponentFormConfig} configuration Daemon component configuration
	 */
	private configuration: ComponentFormConfig = {
		name: '',
		libraryPath: '',
		libraryName: '',
		enabled: false,
		startlevel: 0
	};
	
	/**
	 * @property {string} component Daemon component name for editing
	 */
	@Prop({ required: false, default: '' }) component!: string;
	
	/**
	 * Computes page title depending on the action (add, edit)
	 * @returns {string} Page title
	 */
	get pageTitle(): string {
		return this.$route.path === '/config/daemon/component/add' ?
			this.$t('config.daemon.components.add').toString() : this.$t('config.daemon.components.edit').toString();
	}

	/**
	 * Computes the text of form submit button depending on the action (add, edit)
	 * @returns {string} Button text
	 */
	get submitButton(): string {
		return this.$route.path === '/config/daemon/component/add' ?
			this.$t('forms.add').toString() : this.$t('forms.edit').toString();
	}

	/**
	 * Vue lifecycle hook created
	 */
	created(): void {
		extend('integer', integer);
		extend('required', required);
	}

	/**
	 * Vue lifecycle hook mounted
	 */
	mounted(): void {
		if (this.component !== '') {
			this.getComponent();
		}
	}

	/**
	 * Retrieves Daemon component configuration
	 */
	private getComponent(): void {
		this.$store.commit('spinner/SHOW');
		DaemonConfigurationService.getComponent(this.component)
			.then((response: AxiosResponse) => {
				this.$store.commit('spinner/HIDE');
				this.configuration = response.data.configuration;
			})
			.catch((error: AxiosError) => {
				this.$router.push('/config/daemon/component/');
				FormErrorHandler.configError(error);
			});
	}

	/**
	 * Saves new or updates existing Daemon component configuration
	 */
	private saveComponent(): void {
		this.$store.commit('spinner/SHOW');
		if (this.component !== '') {
			DaemonConfigurationService.updateComponent(this.component, this.configuration)
				.then(() => this.successfulSave())
				.catch((error: AxiosError) => FormErrorHandler.configError(error));
		} else {
			DaemonConfigurationService.createComponent(this.configuration)
				.then(() => this.successfulSave())
				.catch((error: AxiosError) => FormErrorHandler.configError(error));
		}
	}

	/**
	 * Handles successful REST API response
	 */
	private successfulSave(): void {
		this.$store.commit('spinner/HIDE');
		if (this.$route.path === '/config/daemon/component/add') {
			this.$toast.success(
				this.$t('config.daemon.components.messages.addSuccess', {component: this.configuration.name})
					.toString()
			);
		} else {
			this.$toast.success(
				this.$t('config.daemon.components.messages.editSuccess', {component: this.component})
					.toString()
			);
		}
		this.$router.push('/config/daemon/component/');
	}
}
</script>
