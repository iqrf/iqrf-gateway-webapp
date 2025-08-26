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
	<CCard class='border-0 card-margin-bottom'>
		<CCardBody>
			<CElementCover
				v-if='loadFailed'
				style='z-index: 1;'
				:opacity='0.85'
			>
				{{ $t('config.daemon.messages.failedElement') }}
			</CElementCover>
			<ValidationObserver v-slot='{invalid}'>
				<CForm
					@submit.prevent='saveConfig()'
				>
					<fieldset :disabled='loadFailed'>
						<ValidationProvider
							v-if='isAdmin'
							v-slot='{errors, touched, valid}'
							rules='required'
							:custom-messages='{
								required: $t("config.daemon.misc.iqrfDb.errors.instance.required")
							}'
						>
							<CInput
								v-model='config.instance'
								:label='$t("forms.fields.instanceName")'
								:is-valid='touched ? valid : null'
								:invalid-feedback='errors.join(", ")'
							/>
						</ValidationProvider>
						<CInputCheckbox
							:checked.sync='config.autoEnumerateBeforeInvoked'
							:label='$t("config.daemon.misc.iqrfDb.form.autoEnum")'
						/>
						<CInputCheckbox
							:checked.sync='config.enumerateOnLaunch'
							:label='$t("config.daemon.misc.iqrfDb.form.launchEnum")'
						/>
						<CInputCheckbox
							:checked.sync='config.metadataToMessages'
							:label='$t("config.daemon.misc.iqrfDb.form.metadata")'
						/>
						<CButton type='submit' color='primary' :disabled='invalid'>
							{{ $t('forms.save') }}
						</CButton>
					</fieldset>
				</CForm>
			</ValidationObserver>
		</CCardBody>
	</CCard>
</template>

<script lang='ts'>
import {Component, Vue} from 'vue-property-decorator';
import {CButton, CCard, CCardBody, CCardHeader, CElementCover, CForm, CInput, CInputCheckbox, CSwitch} from '@coreui/vue/src';
import {extend, ValidationObserver, ValidationProvider} from 'vee-validate';

import {extendedErrorToast} from '@/helpers/errorToast';
import {integer, min_value, required} from 'vee-validate/dist/rules';
import {mapGetters} from 'vuex';
import {versionLowerEqual} from '@/helpers/versionChecker';
import {UserRole} from '@/services/AuthenticationService';

import DaemonConfigurationService from '@/services/DaemonConfigurationService';

import {AxiosError, AxiosResponse} from 'axios';

import {
	IqrfGatewayDaemonComponentName,
	IqrfGatewayDaemonDb
} from '@iqrf/iqrf-gateway-webapp-client/types/Config';


@Component({
	components: {
		CButton,
		CCard,
		CCardBody,
		CCardHeader,
		CElementCover,
		CForm,
		CInput,
		CInputCheckbox,
		CSwitch,
		ValidationObserver,
		ValidationProvider,
	},
	computed: {
		...mapGetters({
			daemonVersion: 'daemonClient/getVersion',
		}),
	},
	methods: {
		versionLowerEqual: versionLowerEqual,
	},
})

/**
 * IQRF Info component configuration
 */
export default class IqrfDb extends Vue {

	/**
	 * @constant {string} name IQRF Info component name
	 */
	private name = 'iqrf::IqrfDb';

	/**
	 * @var {string} instance IQRF Info component instance name
	 */
	private instance = '';

	/**
	 * @var {IqrfGatewayDaemonDb} config IQRF Info component instance configuration
	 */
	private config: IqrfGatewayDaemonDb = {
		component: IqrfGatewayDaemonComponentName.IqrfDb,
		instance: '',
		autoEnumerateBeforeInvoked: false,
		enumerateOnLaunch: false,
		metadataToMessages: false,
	};

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
		extend('integer', integer);
		extend('min', min_value);
		extend('required', required);
	}

	/**
	 * Vue lifecycle hook mounted
	 */
	mounted(): void {
		this.getConfig();
	}

	/**
	 * Retrieves configuration of IQRF DB component
	 */
	private getConfig(): Promise<void> {
		return DaemonConfigurationService.getComponent(this.name)
			.then((response: AxiosResponse) => {
				if (response.data.instances.length > 0) {
					this.config = response.data.instances[0];
					this.instance = this.config.instance;
				}
				this.$emit('fetched', {name: 'iqrfDb', success: true});
			})
			.catch(() => {
				this.loadFailed = true;
				this.$emit('fetched', {name: 'iqrfDb', success: false});
			});
	}

	/**
	 * Saves new or updates existing configuration of IQRF DB component instance
	 */
	private saveConfig(): void {
		this.$store.commit('spinner/SHOW');
		if (this.instance !== '') {
			DaemonConfigurationService.updateInstance(this.name, this.instance, this.config)
				.then(this.handleSuccess)
				.catch(this.handleFailure);
		} else {
			DaemonConfigurationService.createInstance(this.name, this.config)
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
				this.$t('config.daemon.misc.iqrfDb.messages.saveSuccess').toString()
			);
		});
	}

	/**
	 * Handles REST API failure
	 * @param {AxiosError} err Error response
	 */
	private handleFailure(err: AxiosError): void {
		extendedErrorToast(err, 'config.daemon.misc.iqrfDb.messages.saveFailed');
	}
}
</script>
