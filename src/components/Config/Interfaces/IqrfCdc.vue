<!--
Copyright 2017-2025 IQRF Tech s.r.o.
Copyright 2019-2025 MICRORISC s.r.o.

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
		<CCard>
			<CCardHeader>
				{{ $t('config.daemon.interfaces.iqrfCdc.title') }}
			</CCardHeader>
			<CCardBody>
				<CElementCover
					v-if='loadFailed'
					style='z-index: 1;'
					:opacity='0.85'
				>
					{{ $t('config.daemon.messages.failedElement') }}
				</CElementCover>
				<ValidationObserver v-slot='{invalid}'>
					<CForm @submit.prevent='saveConfig'>
						<fieldset :disabled='loadFailed'>
							<ValidationProvider
								v-if='isAdmin'
								v-slot='{errors, touched, valid}'
								rules='required'
								:custom-messages='{
									required: $t("config.daemon.interfaces.iqrfCdc.errors.instance")
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
								rules='required'
								:custom-messages='{
									required: $t("config.daemon.interfaces.iqrfCdc.errors.iqrfInterface")
								}'
							>
								<CInput
									v-model='configuration.IqrfInterface'
									:label='$t("config.daemon.interfaces.iqrfCdc.form.interface")'
									:is-valid='touched ? valid : null'
									:invalid-feedback='errors.join(", ")'
								/>
							</ValidationProvider>
							<CButton
								type='submit'
								color='primary'
								:disabled='invalid'
							>
								{{ $t('forms.save') }}
							</CButton>
						</fieldset>
					</CForm>
				</ValidationObserver>
			</CCardBody>
			<CCardFooter>
				<h4>{{ $t('config.daemon.interfaces.iqrfCdc.mappings' ) }}</h4><hr>
				<InterfacePorts interface-type='cdc' @update-port='updatePort' />
			</CCardFooter>
		</CCard>
	</div>
</template>

<script lang='ts'>
import {Component, Vue} from 'vue-property-decorator';
import {CButton, CCard, CCardBody, CCardHeader, CElementCover, CForm, CInput} from '@coreui/vue/src';
import {extend, ValidationObserver, ValidationProvider} from 'vee-validate';
import InterfacePorts from '@/components/Config/Interfaces/InterfacePorts.vue';

import {extendedErrorToast} from '@/helpers/errorToast';
import {UserRole} from '@/services/AuthenticationService';
import {required} from 'vee-validate/dist/rules';

import DaemonConfigurationService from '@/services/DaemonConfigurationService';

import {AxiosError, AxiosResponse} from 'axios';
import {IIqrfCdc} from '@/interfaces/Config/IqrfInterfaces';

@Component({
	components: {
		CButton,
		CCard,
		CCardBody,
		CCardHeader,
		CElementCover,
		CForm,
		CInput,
		InterfacePorts,
		ValidationObserver,
		ValidationProvider,
	},
})

/**
 * IQRF CDC communication interface configuration component
 */
export default class IqrfCdc extends Vue {
	/**
	 * @constant {string} componentName IQRF CDC interface component name
	 */
	private componentName = 'iqrf::IqrfCdc';

	/**
	 * @var {IIqrfCdc} configuration IQRF CDC interface instance configuration
	 */
	private configuration: IIqrfCdc = {
		component: '',
		instance: '',
		IqrfInterface: '',
	};

	/**
	 * @var {string} instance Name of IQRF CDC component instance
	 */
	private instance = '';

	/**
	 * @var {boolean} loadFailed Indicates whether configuration fetch failed
	 */
	private loadFailed = false;

	/**
	 * Checks if user is an administrator
	 * @returns {boolean} True if user is an administrator
	 */
	get isAdmin(): boolean {
		return this.$store.getters['user/getRole'] === UserRole.ADMIN;
	}

	/**
	 * Vue lifecycle hook created
	 */
	created(): void {
		extend('required', required);
	}

	/**
	 * Vue lifecycle hook mounted
	 */
	mounted(): void {
		this.getConfig();
	}

	/**
	 * Retrieves configuration of IQRF CDC interface component
	 */
	private getConfig(): Promise<void> {
		return DaemonConfigurationService.getComponent(this.componentName)
			.then((response: AxiosResponse) => {
				if (response.data.instances.length > 0) {
					this.configuration = response.data.instances[0];
					this.instance = this.configuration.instance;
				}
				this.$emit('fetched', {name: 'iqrfCdc', success: true});
			})
			.catch(() => {
				this.loadFailed = true;
				this.$emit('fetched', {name: 'iqrfCdc', success: false});
			});
	}

	/**
	 * Saves new or updates existing configuration of IQRF CDC interface component instance
	 */
	private saveConfig(): void {
		this.$store.commit('spinner/SHOW');
		if (this.instance !== '') {
			DaemonConfigurationService.updateInstance(this.componentName, this.instance, this.configuration)
				.then(this.handleSuccess)
				.catch(this.handleFailure);
		} else {
			DaemonConfigurationService.createInstance(this.componentName, this.configuration)
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
				this.$t('config.daemon.interfaces.iqrfCdc.messages.saveSuccess').toString()
			);
		});
	}

	/**
	 * Handles REST API failure
	 * @param {AxiosError} err Error response
	 */
	private handleFailure(err: AxiosError): void {
		extendedErrorToast(err, 'config.daemon.interfaces.iqrfCdc.messages.saveFailed');
	}

	/**
	 * Updates port in configuration from mapping
	 * @param {string} port Port
	 */
	private updatePort(port: string): void {
		this.configuration.IqrfInterface = port;
	}
}
</script>
