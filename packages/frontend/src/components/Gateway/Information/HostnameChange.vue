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
	<ValidationObserver v-slot='{invalid}'>
		<v-dialog
			v-model='render'
			width='50%'
			persistent
			no-click-animation
		>
			<template #activator='{on, attrs}'>
				<v-btn
					color='primary'
					small
					v-bind='attrs'
					v-on='on'
				>
					<v-icon small>
						mdi-pencil
					</v-icon>
					{{ $t('forms.edit') }}
				</v-btn>
			</template>
			<v-card>
				<v-card-title>{{ $t('gateway.hostname.title') }}</v-card-title>
				<v-card-text>
					<form @submit.prevent='save'>
						<ValidationProvider
							v-slot='{errors, touched, valid}'
							rules='hostnamePattern|required'
							:custom-messages='{
								hostnamePattern: $t("gateway.hostname.errors.hostnameInvalid"),
								required: $t("gateway.hostname.errors.hostnameMissing"),
							}'
						>
							<v-text-field
								v-model='hostname'
								:label='$t("gateway.info.hostname")'
								:success='touched ? valid : null'
								:error-messages='errors'
							/>
						</ValidationProvider>
						<v-checkbox
							v-model='setIdeCounterpart'
							:label='$t("gateway.hostname.setIdeCounterpart")'
						/>
					</form>
				</v-card-text>
				<v-card-actions>
					<v-spacer />
					<v-btn @click='hide'>
						{{ $t('forms.cancel') }}
					</v-btn>
					<v-btn color='primary' :disabled='invalid' @click='save'>
						{{ $t('forms.save') }}
					</v-btn>
				</v-card-actions>
			</v-card>
		</v-dialog>
	</ValidationObserver>
</template>

<script lang='ts'>
import {AxiosError, AxiosResponse} from 'axios';
import {extend, ValidationObserver, ValidationProvider} from 'vee-validate';
import {required} from 'vee-validate/dist/rules';
import {Component, Prop, Vue, Watch} from 'vue-property-decorator';

import {extendedErrorToast} from '@/helpers/errorToast';
import {machineHostname} from '@/helpers/validationRules/Gateway';
import DaemonConfigurationService from '@/services/DaemonConfigurationService';
import {IIdeCounterpart} from '@/interfaces/Config/IdeCounterpart';
import {useApiClient} from '@/services/ApiClient';

@Component({
	components: {
		ValidationObserver,
		ValidationProvider,
	},
})

/**
 * Hostname changer component
 */
export default class HostnameChange extends Vue {

	/**
	 * @property {string|null} current Current hostname
	 */
	@Prop({ default: null, type: String }) current!: string|null;

	/**
	 * @var {boolean} render Controls whether or not modal is rendered
	 */
	private render = false;


	/**
	 * @property {boolean} setIdeCounterpart Controls whether or not to set IQRF IDE counterpart gwIdentName
	 */
	private setIdeCounterpart: boolean = false;

	/**
	 * @property {string} hostname Hostname to set
	 */
	private hostname: string = '';

	/**
	 * Initializes validation rules
	 */
	created(): void {
		extend('hostnamePattern', machineHostname);
		extend('required', required);
	}

	@Watch('current')
	private loadCurrent(newVal: string|null): void {
		this.hostname = newVal?.split('.', 1)[0] ?? '';
	}

	/**
	 * Sets new hostname configuration
	 */
	private async save(): Promise<void> {
		this.$store.commit('spinner/SHOW');
		Promise.all([
			this.setIdeCounterpartConfig(),
			useApiClient().getGatewayServices().getHostnameService().updateHostname(this.hostname)
		])
			.then(() => {
				this.$store.commit('spinner/HIDE');
				this.$toast.success(
					this.$t('gateway.hostname.messages.success').toString()
				);
				if (this.setIdeCounterpart) {
					this.$toast.info(
						this.$t('gateway.hostname.messages.daemonRestart').toString()
					);
				}
				this.hide();
				this.$emit('hostname-changed');
			})
			.catch((error: AxiosError) => {
				extendedErrorToast(error, 'gateway.hostname.messages.failed');
			});
	}

	/**
	 * Sets a new hostname for IDE counterpart component
	 */
	private async setIdeCounterpartConfig(): Promise<void> {
		if (!this.setIdeCounterpart) {
			return;
		}
		const componentConfig: IIdeCounterpart|null = await DaemonConfigurationService.getComponent('iqrf::IdeCounterpart')
			.then((response: AxiosResponse<{instances: IIdeCounterpart[]}>) => response.data.instances[0] ?? null);
		if (componentConfig === null) {
			return;
		}
		componentConfig.gwIdentNetBios = this.hostname;
		await DaemonConfigurationService.updateInstance('iqrf::IdeCounterpart', componentConfig.instance, componentConfig);
	}

	/**
	 * Shows modal component
	 */
	public show(): void {
		this.render = true;
	}

	/**
	 * Hides modal component
	 */
	public hide(): void {
		this.render = false;
	}

}
</script>
