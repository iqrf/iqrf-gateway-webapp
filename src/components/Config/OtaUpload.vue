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
	<v-card :loading='loading'>
		<v-card-title>
			{{ $t("config.daemon.misc.otaUpload.title") }}
		</v-card-title>
		<v-card-text v-if='!loading'>
			<v-overlay
				v-if='loadFailed'
				:opacity='0.65'
				absolute
			>
				{{ $t('config.daemon.messages.failedElement') }}
			</v-overlay>
			<ValidationObserver v-slot='{invalid}'>
				<form @submit.prevent='saveConfig'>
					<fieldset :disabled='loadFailed'>
						<ValidationProvider
							v-slot='{errors, touched, valid}'
							rules='required'
							:custom-messages='{
								required: $t("config.daemon.misc.otaUpload.errors.instance"),
							}'
						>
							<v-text-field
								v-model='configuration.instance'
								:label='$t("forms.fields.instanceName")'
								:success='touched ? valid : null'
								:error-messages='errors'
							/>
						</ValidationProvider>
						<v-text-field
							v-model='configuration.uploadPath'
							:label='$t("config.daemon.misc.otaUpload.form.uploadPath")'
						/>
						<v-btn
							type='submit'
							color='primary'
							:disabled='invalid'
						>
							{{ $t('forms.save') }}
						</v-btn>
					</fieldset>
				</form>
			</ValidationObserver>
		</v-card-text>
	</v-card>
</template>

<script lang='ts'>
import {Component, Vue} from 'vue-property-decorator';
import {extend, ValidationObserver, ValidationProvider} from 'vee-validate';

import {extendedErrorToast} from '@/helpers/errorToast';
import {required} from 'vee-validate/dist/rules';

import DaemonConfigurationService from '@/services/DaemonConfigurationService';

import {AxiosError, AxiosResponse} from 'axios';
import {IOtaUpload} from '@/interfaces/iqmeshServices';

@Component({
	components: {
		ValidationObserver,
		ValidationProvider,
	}
})

/**
 * OtaUpload card for IqmeshServices component
 */
export default class OtaUpload extends Vue {

	/**
	 * @constant {string} componentName name of daemon component
	 */
	private componentName = 'iqrf::OtaUploadService';

	/**
	 * @var {string} instance name of daemon component instance
	 */
	private instance = '';

	/**
	 * @var {IOtaUpload} configuration OtaUpload instance configuration
	 */
	private configuration: IOtaUpload = {
		component: '',
		instance: '',
		uploadPath: '',
		uploadPathSuffix: '',
	};

	/**
	 * @var {boolean} loadFailed Indicates whether configuration fetch failed
	 */
	private loadFailed = false;

	/**
	 * @var {boolean} loading Flag for loading state
	 */
	private loading = false;

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
		this.loading = true;
		this.getConfig();
	}

	/**
	 * Retrieves instance of OtaUpload daemon component
	 */
	private getConfig(): Promise<void> {
		return DaemonConfigurationService.getComponent(this.componentName)
			.then((response: AxiosResponse) => {
				if (response.data.instances.length > 0) {
					this.configuration = response.data.instances[0];
					this.instance = this.configuration.instance;
				}
				this.loading = false;
			})
			.catch(() => {
				this.loading = false;
				this.loadFailed = true;
				this.$toast.error(
					this.$t('config.daemon.messages.configFetchFailed', {children: 'otaUpload'})
						.toString()
				);
			});
	}

	/**
	 * Updates configuration of OtaUpload instance and creates one if it does not exist
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
			this.$store.commit('spinner/HIDE');
			this.$toast.success(
				this.$t('config.daemon.misc.otaUpload.messages.saveSuccess').toString()
			);
		});
	}

	/**
	 * Handles REST API failure
	 * @param {AxiosError} err Error response
	 */
	private handleFailure(err: AxiosError): void {
		extendedErrorToast(err, 'config.daemon.misc.otaUpload.messages.saveFailed');
	}
}
</script>
