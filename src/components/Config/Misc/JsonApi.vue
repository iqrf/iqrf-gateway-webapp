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
	<CCard body-wrapper class='border-0 card-margin-bottom'>
		<CElementCover
			v-if='loadFailed'
			style='z-index: 1;'
			:opacity='0.85'
		>
			{{ $t('config.daemon.messages.failedElement') }}
		</CElementCover>
		<ValidationObserver
			v-slot='{invalid}'
		>
			<CForm @submit.prevent='saveConfig'>
				<ValidationProvider
					v-slot='{errors, touched, valid}'
					rules='required'
					:custom-messages='{
						required: $t("config.daemon.misc.jsonSplitter.errors.insId"),
					}'
				>
					<CInput
						v-model='insId'
						:label='$t("config.daemon.misc.jsonSplitter.form.insId")'
						:is-valid='touched ? valid : null'
						:invalid-feedback='errors.join(", ")'
					/>
				</ValidationProvider>
				<CInputCheckbox
					:checked.sync='asyncDpaMessage'
					:label='$t("config.daemon.misc.jsonRawApi.form.asyncDpaMessage").toString()'
				/>
				<CInputCheckbox
					:checked.sync='validateJsonResponse'
					:label='$t("config.daemon.misc.jsonSplitter.form.validateJsonResponse").toString()'
				/>
				<CButton
					type='submit'
					color='primary'
					:disabled='invalid'
				>
					{{ $t('forms.save') }}
				</CButton>
			</CForm>
		</ValidationObserver>
	</CCard>
</template>

<script lang='ts'>
import {Component, Vue} from 'vue-property-decorator';
import {CButton, CCard, CCardBody, CCardHeader, CElementCover,CForm, CInput, CInputCheckbox} from '@coreui/vue/src';
import {extend, ValidationObserver, ValidationProvider} from 'vee-validate';

import {extendedErrorToast} from '@/helpers/errorToast';
import {required} from 'vee-validate/dist/rules';
import DaemonConfigurationService from '@/services/DaemonConfigurationService';

import {AxiosError, AxiosResponse} from 'axios';
import {IJsonRaw, IJsonSplitter} from '@/interfaces/jsonApi';


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
		ValidationObserver,
		ValidationProvider,
	}
})

/**
 * JSON API config component for normal user
 */
export default class JsonApi extends Vue {
	/**
	 * @constant {Record<string, string>} componentNames Array of JSON component names
	 */
	private componentNames: Record<string, string> = {
		rawApi: 'iqrf::JsonDpaApiRaw',
		splitter: 'iqrf::JsonSplitter'
	};

	/**
	 * @var {string} insId JSON splitter instance ID
	 */
	private insId = '';

	/**
	 * @var {IJsonRaw|null} rawApi JSON raw api configuration object
	 */
	private rawApi: IJsonRaw|null = null;

	/**
	 * @var {IJsonSplitter|null} splitter JSON splitter configuration object
	 */
	private splitter: IJsonSplitter|null = null;

	/**
	 * @var {boolean} asyncDpaMessage Asynchronous DPA messages?
	 */
	private asyncDpaMessage = false;

	/**
	 * @var {boolean} validateJsonResponse Should JSON responses be validated?
	 */
	private validateJsonResponse = false;

	/**
	 * @var {boolean} loadFailed Indicates whether configuration fetch failed
	 */
	private loadFailed = false;

	/**
	 * Initializes validation rules
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
	 * Retrieves configuration of JSON API components
	 */
	private getConfig(): Promise<void> {
		const requests = [
			DaemonConfigurationService.getComponent(this.componentNames.rawApi),
			DaemonConfigurationService.getComponent(this.componentNames.splitter),
		];
		return Promise.all(requests)
			.then((responses: Array<AxiosResponse>) => {
				this.rawApi = responses[0].data.instances[0];
				this.asyncDpaMessage = responses[0].data.instances[0].asyncDpaMessage;
				this.splitter = responses[1].data.instances[0];
				this.insId = responses[1].data.instances[0].insId;
				this.validateJsonResponse = responses[1].data.instances[0].validateJsonResponse;
				this.$emit('fetched', {name: 'jsonApi', success: true});
			})
			.catch(() => {
				this.loadFailed = true;
				this.$emit('fetched', {name: 'jsonApi', success: false});
			});
	}

	/**
	 * Updates configuration of JSON API component instances
	 */
	private saveConfig(): void {
		const requests: Array<Promise<AxiosResponse>> = [];
		if (this.rawApi !== null && this.asyncDpaMessage !== this.rawApi.asyncDpaMessage) {
			this.rawApi.asyncDpaMessage = this.asyncDpaMessage;
			requests.push(DaemonConfigurationService.updateInstance(this.componentNames.rawApi, this.rawApi.instance, this.rawApi));
		}
		if (this.splitter !== null &&
				(this.validateJsonResponse !== this.splitter.validateJsonResponse || this.insId !== this.splitter.insId)) {
			this.splitter.insId = this.insId;
			this.splitter.validateJsonResponse = this.validateJsonResponse;
			requests.push(DaemonConfigurationService.updateInstance(this.componentNames.splitter, this.splitter.instance, this.splitter));
		}
		if (requests.length === 0) {
			this.$toast.info(
				this.$t('config.daemon.misc.jsonApi.messages.saveNoChanges').toString()
			);
			return;
		}
		this.$store.commit('spinner/SHOW');
		Promise.all(requests)
			.then(() => {
				this.getConfig().then(() => {
					this.$toast.success(
						this.$t('config.daemon.misc.jsonApi.messages.saveSuccess').toString()
					);
				});
			})
			.catch((error: AxiosError) => extendedErrorToast(error, 'config.daemon.misc.jsonApi.messages.saveFailed'));
	}
}
</script>
