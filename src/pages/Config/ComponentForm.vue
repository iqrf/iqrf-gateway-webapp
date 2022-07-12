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
		<v-card>
			<v-card-text>
				<ValidationObserver v-slot='{invalid}'>
					<form @submit.prevent='saveConfig'>
						<ValidationProvider
							v-slot='{errors, touched, valid}'
							rules='required'
							:custom-messages='{
								required: $t("config.daemon.components.errors.name"),
							}'
						>
							<v-text-field
								v-model='configuration.name'
								:label='$t("config.daemon.components.form.name")'
								:success='touched ? valid : null'
								:error-messages='errors'
							/>
						</ValidationProvider>
						<v-text-field
							v-model='configuration.libraryPath'
							:label='$t("config.daemon.components.form.libraryPath")'
						/>
						<ValidationProvider
							v-slot='{errors, touched, valid}'
							rules='required'
							:custom-messages='{
								required: $t("config.daemon.components.errors.libraryName"),
							}'
						>
							<v-text-field
								v-model='configuration.libraryName'
								:label='$t("config.daemon.components.form.libraryName")'
								:success='touched ? valid : null'
								:error-messages='errors'
							/>
						</ValidationProvider>
						<v-checkbox
							v-model='configuration.enabled'
							:label='$t("states.enabled")'
						/>
						<ValidationProvider
							v-slot='{errors, touched, valid}'
							rules='integer|required'
							:custom-messages='{
								integer: $t("forms.errors.integer"),
								required: $t("config.daemon.components.errors.startLevel"),
							}'
						>
							<v-text-field
								v-model.number='configuration.startlevel'
								type='number'
								:label='$t("config.daemon.components.form.startLevel")'
								:success='touched ? valid : null'
								:error-messages='errors'
							/>
						</ValidationProvider>
						<v-btn
							type='submit'
							color='primary'
							:disabled='invalid'
						>
							{{ submitButton }}
						</v-btn>
					</form>
				</ValidationObserver>
			</v-card-text>
		</v-card>
	</div>
</template>

<script lang='ts'>
import {Component, Prop, Vue} from 'vue-property-decorator';
import {extend, ValidationObserver, ValidationProvider} from 'vee-validate';

import {extendedErrorToast} from '@/helpers/errorToast';
import {integer, required} from 'vee-validate/dist/rules';

import DaemonConfigurationService from '@/services/DaemonConfigurationService';

import {AxiosError, AxiosResponse} from 'axios';
import {MetaInfo} from 'vue-meta/types/vue-meta';

interface ComponentFormConfig {
	name: string
	libraryPath: string
	libraryName: string
	enabled: boolean
	startlevel: number
}

@Component({
	components: {
		ValidationObserver,
		ValidationProvider,
	},
	metaInfo(): MetaInfo {
		return {
			title: (this as unknown as ComponentForm).pageTitle
		};
	},
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
			this.getConfig();
		}
	}

	/**
	 * Retrieves Daemon component configuration
	 */
	private getConfig(): Promise<void> {
		this.$store.commit('spinner/SHOW');
		return DaemonConfigurationService.getComponent(this.component)
			.then((response: AxiosResponse) => {
				this.$store.commit('spinner/HIDE');
				this.configuration = response.data.configuration;
			})
			.catch((error: AxiosError) => {
				this.$router.push('/config/daemon/component/');
				extendedErrorToast(error, 'config.daemon.components.messages.getFailed', {component: this.component});
			});
	}

	/**
	 * Saves new or updates existing Daemon component configuration
	 */
	private saveConfig(): void {
		this.$store.commit('spinner/SHOW');
		if (this.component !== '') {
			DaemonConfigurationService.updateComponent(this.component, this.configuration)
				.then(this.handleSuccess)
				.catch(this.handleFailure);
		} else {
			DaemonConfigurationService.createComponent(this.configuration)
				.then(this.handleSuccess)
				.catch(this.handleFailure);
		}
	}

	/**
	 * Handles REST API success
	 */
	private handleSuccess(): void {
		this.getConfig().then(() => {
			this.$toast.success(
				this.$t('config.daemon.components.messages.saveSuccess', {component: this.configuration.name}).toString()
			);
			this.$router.push('/config/daemon/component/');
		});
	}

	/**
	 * Handles REST API failure
	 * @param {AxiosError} err Error response
	 */
	private handleFailure(err: AxiosError): void {
		extendedErrorToast(err, 'config.daemon.components.messages.saveFailed', {component: this.configuration.name});
	}
}
</script>
