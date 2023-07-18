<template>
	<ValidationObserver v-slot='{invalid}'>
		<CModal
			:show.sync='render'
			color='primary'
		>
			<template #header>
				<h5 class='modal-title'>
					{{ $t('gateway.hostname.title') }}
				</h5>
			</template>
			<CForm @submit.prevent='save'>
				<ValidationProvider
					v-slot='{errors, touched, valid}'
					rules='hostnamePattern|required'
					:custom-messages='{
						hostnamePattern: $t("gateway.hostname.errors.hostnameInvalid"),
						required: $t("gateway.hostname.errors.hostnameMissing"),
					}'
				>
					<CInput
						v-model='config.hostname'
						:label='$t("gateway.info.hostname")'
						:is-valid='touched ? valid : null'
						:invalid-feedback='errors.join(", ")'
					/>
				</ValidationProvider>
				<CInputCheckbox
					:checked.sync='setJsonSplitter'
					:label='$t("gateway.hostname.setJsonSplitter")'
				/>
				<CInputCheckbox
					:checked.sync='setIdeCounterpart'
					:label='$t("gateway.hostname.setIdeCounterpart")'
				/>
			</CForm>
			<template #footer>
				<CButton
					color='primary'
					:disabled='invalid'
					@click='save'
				>
					{{ $t('forms.save') }}
				</CButton> <CButton
					color='secondary'
					@click='hide'
				>
					{{ $t('forms.cancel') }}
				</CButton>
			</template>
		</CModal>
	</ValidationObserver>
</template>

<script lang='ts'>
import {Component, Prop, Vue, Watch} from 'vue-property-decorator';
import {CButton, CForm, CInput, CInputCheckbox, CModal} from '@coreui/vue/src';
import {extend, ValidationObserver, ValidationProvider} from 'vee-validate';

import {extendedErrorToast} from '@/helpers/errorToast';
import {required} from 'vee-validate/dist/rules';
import {machineHostname} from '@/helpers/validationRules/Gateway';

import GatewayService from '@/services/GatewayService';

import {IHostname} from '@/interfaces/Gateway/Information';
import {AxiosError, AxiosResponse} from 'axios';
import DaemonConfigurationService from '@/services/DaemonConfigurationService';
import {IJsonSplitter} from '@/interfaces/Config/JsonApi';
import {IIdeCounterpart} from '@/interfaces/Config/IdeCounterpart';


@Component({
	components: {
		CButton,
		CForm,
		CInput,
		CInputCheckbox,
		CModal,
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
	 * @property {boolean} setJsonSplitter Controls whether or not to set JsonSplitter instance ID
   */
	private setJsonSplitter: boolean = false;

	/**
	 * @property {boolean} setIdeCounterpart Controls whether or not to set IQRF IDE counterpart gwIdentName
   */
	private setIdeCounterpart: boolean = false;

	/**
	 * @var {IHostname} config Hostnamectl configuration
	 */
	private config: IHostname = {
		hostname: ''
	};

	/**
	 * Initializes validation rules
	 */
	created(): void {
		extend('hostnamePattern', machineHostname);
		extend('required', required);
	}

	@Watch('current')
	private loadCurrent(newVal: string|null): void {
		this.config.hostname = newVal?.split('.', 1)[0] ?? '';
	}

	/**
	 * Sets new hostname configuration
	 */
	private async save(): Promise<void> {
		this.$store.commit('spinner/SHOW');
		Promise.all([
			this.setJsonSplitterConfig(),
			this.setIdeCounterpartConfig(),
			GatewayService.setHostname(this.config)
		])
			.then(() => {
				this.$store.commit('spinner/HIDE');
				this.$toast.success(
					this.$t('gateway.hostname.messages.success').toString()
				);
				if (this.setJsonSplitter || this.setIdeCounterpart) {
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
	 * Sets a new hostname as IDE counterpart gwIdentName
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
		componentConfig.gwIdentName = this.config.hostname;
		await DaemonConfigurationService.updateInstance('iqrf::IdeCounterpart', componentConfig.instance, componentConfig);
	}

	/**
	 * Sets a new hostname as JsonSplitter instance ID
   */
	private async setJsonSplitterConfig(): Promise<void> {
		if (!this.setJsonSplitter) {
			return;
		}
		const componentConfig: IJsonSplitter|null = await DaemonConfigurationService.getComponent('iqrf::JsonSplitter')
			.then((response: AxiosResponse<{instances: IJsonSplitter[]}>) => response.data.instances[0] ?? null);
		if (componentConfig === null) {
			return;
		}
		componentConfig.insId = this.config.hostname;
		await DaemonConfigurationService.updateInstance('iqrf::JsonSplitter', componentConfig.instance, componentConfig);
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
