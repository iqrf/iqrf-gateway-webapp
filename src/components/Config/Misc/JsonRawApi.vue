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
	<v-card :loading='loading' flat tile>
		<v-card-title>
			{{ $t('config.daemon.misc.jsonRawApi.title') }}
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
				<v-form @submit.prevent='saveConfig'>
					<fieldset :disabled='loadFailed'>
						<ValidationProvider
							v-slot='{errors, touched, valid}'
							rules='required'
							:custom-messages='{
								required: $t("config.daemon.misc.jsonRawApi.errors.instance"),
							}'
						>
							<v-text-field
								v-model='configuration.instance'
								:label='$t("forms.fields.instanceName")'
								:success='touched ? valid : null'
								:error-messages='errors'
							/>
						</ValidationProvider>
						<v-checkbox
							v-model='configuration.asyncDpaMessage'
							:label='$t("config.daemon.misc.jsonRawApi.form.asyncDpaMessage")'
							dense
						/>
						<v-btn
							type='submit'
							color='primary'
							:disabled='invalid'
						>
							{{ $t('forms.save') }}
						</v-btn>
					</fieldset>
				</v-form>
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
import {IJsonRaw} from '@/interfaces/Config/JsonApi';


@Component({
	components: {
		ValidationObserver,
		ValidationProvider
	}
})

/**
 * JSON RawApi component configuration
 */
export default class JsonRawApi extends Vue {
	/**
	 * @constant {string} componentName JSON RawApi component name
	 */
	private componentName = 'iqrf::JsonDpaApiRaw';

	/**
	 * @var {string} instances JSON RawApi component instance name
	 */
	private instance = '';

	/**
	 * @var {IJsonRaw} configuration JSON RawApi component instance configuration
	 */
	private configuration: IJsonRaw = {
		instance: '',
		asyncDpaMessage: false,
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
	 * Initializes validation rules
	 */
	created(): void {
		extend('required', required);
	}

	/**
	 * Loads component configuration
	 */
	mounted(): void {
		this.loading = true;
		this.getConfig();
	}

	/**
	 * Retrieves configuration of JSON RawApi component
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
					this.$t('config.daemon.messages.configFetchFailed', {children: 'jsonRawApi'})
						.toString()
				);
			});
	}

	/**
	 * Saves new or updates existing configuration of JSON RawApi component instance
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
				this.$t('config.daemon.misc.jsonRawApi.messages.saveSuccess').toString()
			);
		});
	}

	/**
	 * Handles REST API failure
	 * @param {AxiosError} err Error response
	 */
	private handleFailure(err: AxiosError): void {
		extendedErrorToast(err, 'config.daemon.misc.jsonRawApi.messages.saveFailed');
	}
}
</script>
