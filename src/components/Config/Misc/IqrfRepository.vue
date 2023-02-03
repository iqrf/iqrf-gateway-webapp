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
				<CForm>
					<fieldset :disabled='loadFailed'>
						<ValidationProvider
							v-if='isAdmin'
							v-slot='{ errors, touched, valid }'
							rules='required'
							:custom-messages='{
								required: $t("config.daemon.misc.iqrfRepository.errors.instance")
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
							:custom-messages='{required: $t("config.daemon.misc.iqrfRepository.errors.urlRepo")}'
						>
							<CInput
								v-model='configuration.urlRepo'
								:label='$t("config.daemon.misc.iqrfRepository.form.urlRepo")'
								:is-valid='touched ? valid : null'
								:invalid-feedback='errors.join(", ")'
							/>
						</ValidationProvider>
						<div class='form-group'>
							<label for='checkEnableSwitch'>
								{{ $t("config.daemon.misc.iqrfRepository.form.enableCheck") }}
							</label><br>
							<CSwitch
								id='checkEnableSwitch'
								color='primary'
								size='lg'
								shape='pill'
								label-on='ON'
								label-off='OFF'
								:checked.sync='checkEnabled'
							/>
						</div>
						<ValidationProvider
							v-if='checkEnabled'
							v-slot='{errors, touched, valid}'
							rules='integer|required|min:0'
							:custom-messages='{
								integer: $t("forms.errors.integer"),
								required: $t("config.daemon.misc.iqrfRepository.errors.checkPeriod"),
								min: $t("config.daemon.misc.iqrfRepository.errors.checkPeriod"),
							}'
						>
							<CInput
								v-model.number='configuration.checkPeriodInMinutes'
								type='number'
								min='0'
								:label='$t("config.daemon.misc.iqrfRepository.form.checkPeriod")'
								:is-valid='touched ? valid : null'
								:invalid-feedback='errors.join(", ")'
							/>
						</ValidationProvider>
						<CInputCheckbox
							:checked.sync='configuration.downloadIfRepoCacheEmpty'
							:label='$t("config.daemon.misc.iqrfRepository.form.downloadIfEmpty")'
						/>
						<CButton
							class='mr-1'
							color='primary'
							:disabled='invalid'
							@click='saveConfig'
						>
							{{ $t('forms.save') }}
						</CButton>
						<CButton
							color='primary'
							@click='updateCache'
						>
							{{ $t('config.daemon.misc.iqrfRepository.cacheUpdate') }}
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

import {buildDaemonMessageOptions} from '@/store/modules/daemonClient.module';
import {extendedErrorToast} from '@/helpers/errorToast';
import {integer, min_value, required} from 'vee-validate/dist/rules';
import {UserRole} from '@/services/AuthenticationService';

import DaemonConfigurationService from '@/services/DaemonConfigurationService';
import ManagementService from '@/services/DaemonApi/ManagementService';

import {AxiosError, AxiosResponse} from 'axios';
import {IIqrfRepository} from '@/interfaces/Config/Misc';
import {MutationPayload} from 'vuex';

/**
 * IQRF Repository component configuration
 */
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
})
export default class IqrfRepository extends Vue {
	/**
	 * @var {string} msgId Daemon API message ID
	 */
	private msgId = '';

	/**
	 * @constant {string} name IQRF Repository component name, used for REST API communication
	 */
	private name = 'iqrf::JsCache';

	/**
	 * @var {string} instance IQRF Repository component instance name
	 */
	private instance = '';

	/**
	 * @var {IIqrfRepository} configuration IQRF Repository component instance configuration
	 */
	private configuration: IIqrfRepository = {
		component: '',
		instance: '',
		urlRepo: '',
		checkPeriodInMinutes: 0,
		downloadIfRepoCacheEmpty: true,
	};

	/**
	 * @var {boolean} checkEnabled Enable periodical update check
	 */
	private checkEnabled = false;

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
	 * Component unsubscribe function
	 */
	private unsubscribe: CallableFunction = () => {return;};

	/**
	 * Initializes validation rules and mutation handler
	 */
	created(): void {
		extend('integer', integer);
		extend('min', min_value);
		extend('required', required);
		this.unsubscribe = this.$store.subscribe((mutation: MutationPayload) => {
			if (mutation.type !== 'daemonClient/SOCKET_ONMESSAGE') {
				return;
			}
			if (mutation.payload.data.msgId !== this.msgId) {
				return;
			}
			this.$store.dispatch('daemonClient/removeMessage', this.msgId);
			this.$store.dispatch('spinner/hide');
			if (mutation.payload.mType === 'mngDaemon_UpdateCache') {
				this.handleUpdateCacheResponse(mutation.payload.data);
			}
		});
	}

	/**
	 * Retrieves iqrf repository configuration
	 */
	mounted(): void {
		this.getConfig();
	}

	/**
	 * Unregisters mutation handling
	 */
	beforeDestroy(): void {
		this.$store.dispatch('daemonClient/removeMessage', this.msgId);
		this.unsubscribe();
	}

	/**
	 * Retrieves configuration of IQRF Repository component
	 */
	private getConfig(): Promise<void> {
		return DaemonConfigurationService.getComponent(this.name)
			.then((response: AxiosResponse) => {
				if (response.data.instances.length > 0) {
					this.parseConfiguration(response.data.instances[0]);
				}
				this.$emit('fetched', {name: 'iqrfRepository', success: true});
			})
			.catch(() => {
				this.loadFailed = true;
				this.$emit('fetched', {name: 'iqrfRepository', success: false});
			});
	}

	/**
	 * Parses IQRF Repository configuration from REST API response
	 * @param {IIqrfRepository} response Configuration from REST API response
	 */
	private parseConfiguration(response: IIqrfRepository): void {
		this.instance = response.instance;
		this.configuration = response;
		if (this.configuration.checkPeriodInMinutes !== undefined && this.configuration.checkPeriodInMinutes > 0) {
			this.checkEnabled = true;
		}
	}

	/**
	 * Saves new or updates existing configuration of IQRF Repository component instance
	 */
	private saveConfig(): void {
		if (!this.checkEnabled) {
			this.configuration.checkPeriodInMinutes = 0;
		}
		this.$store.commit('spinner/SHOW');
		if (this.instance !== '') {
			DaemonConfigurationService.updateInstance(this.name, this.instance, this.configuration)
				.then(this.handleSuccess)
				.catch(this.handleFailure);
		} else {
			DaemonConfigurationService.createInstance(this.name, this.configuration)
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
				this.$t('config.daemon.misc.iqrfRepository.messages.saveSuccess').toString()
			);
		});
	}

	/**
	 * Handles REST API failure
	 * @param {AxiosError} err Error response
	 */
	private handleFailure(err: AxiosError): void {
		extendedErrorToast(err, 'config.daemon.misc.iqrfRepository.messages.saveFailed');
	}

	/**
	 * Sends cache update request
	 */
	private updateCache(): void {
		const options = buildDaemonMessageOptions(60000, 'config.daemon.misc.iqrfRepository.messages.cacheUpdateTimeout', () => this.msgId = '');
		this.$store.dispatch('spinner/show', {timeout: 60000});
		ManagementService.updateCache(options)
			.then((msgId: string) => this.msgId = msgId);
	}

	/**
	 * Handles cache update response
	 * @param response Response
	 */
	private handleUpdateCacheResponse(response): void {
		if (response.status === 0) {
			const updated: boolean = response.rsp.updated;
			this.$toast.success(
				this.$t(
					'config.daemon.misc.iqrfRepository.messages.cacheUpdate' + (updated ? 'Success' : 'NotNeeded')
				).toString()
			);
		} else {
			this.$toast.error(
				this.$t(
					'config.daemon.misc.iqrfRepository.messages.cacheUpdateFailed',
					{error: response.errorStr}
				).toString()
			);
		}
	}
}
</script>
