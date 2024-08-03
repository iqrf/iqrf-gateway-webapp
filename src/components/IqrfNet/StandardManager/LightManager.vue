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
			<ValidationObserver v-slot='{invalid}'>
				<CForm>
					<CSelect
						v-model='dpaCommand'
						:options='dpaCommandOptions'
						:label='$t("iqrfnet.standard.light.form.dpaCommand.title")'
					/>
					<ValidationProvider
						v-slot='{errors, touched, valid}'
						rules='integer|required|between:1,239'
						:custom-messages='{
							between: $t("forms.errors.deviceAddr.between"),
							integer: $t("forms.errors.integer"),
							required: $t("forms.errors.deviceAddr.required"),
						}'
					>
						<CInput
							v-model.number='address'
							type='number'
							min='1'
							max='239'
							:label='$t("forms.fields.deviceAddr")'
							:is-valid='touched ? valid : null'
							:invalid-feedback='errors.join(", ")'
						/>
					</ValidationProvider>
					<div v-if='dpaCommand === StandardLightDpaCommands.SendLdi'>
						<ValidationProvider
							v-for='i of commands.length'
							:key='i'
							v-slot='{errors, touched, valid}'
							rules='integer|required|between:0,65535'
							:custom-messages='{
								between: $t("iqrfnet.standard.light.errors.ldiCommand.between"),
								integer: $t("iqrfnet.standard.light.errors.ldiCommand.between"),
								required: $t("iqrfnet.standard.light.errors.ldiCommand.required"),
							}'
						>
							<CInput
								v-model.number='commands[i-1]'
								type='number'
								min='0'
								max='65535'
								:label='$t("iqrfnet.standard.light.form.ldiCommand.title")'
								:is-valid='touched ? valid : null'
								:invalid-feedback='errors.join(", ")'
							>
								<template #prepend-content>
									<span
										v-if='i === commands.length'
										class='text-success'
										@click='addLdiCommand'
									>
										<FontAwesomeIcon :icon='["far", "square-plus"]' size='xl' />
									</span>
								</template>
								<template #append-content>
									<span
										v-if='commands.length > 1'
										class='text-danger'
										@click='removeLdiCommand(i-1)'
									>
										<FontAwesomeIcon :icon='["far", "trash-alt"]' size='lg' />
									</span>
								</template>
							</CInput>
						</ValidationProvider>
						<CButton
							class='mr-1'
							color='primary'
							:disabled='invalid'
							@click='sendLdiCommands'
						>
							{{ $t('forms.send') }}
						</CButton>
						<CButton
							color='primary'
							:disabled='answers.length === 0'
							@click='showLdiCommandsResult'
						>
							{{ $t('forms.showLast') }}
						</CButton>
					</div>
					<div v-else-if='dpaCommand === StandardLightDpaCommands.SetLai'>
						<ValidationProvider
							v-slot='{errors, touched, valid}'
							rules='double|required|between:0,10'
							:custom-messages='{
								between: $t("iqrfnet.standard.light.errors.voltage.between"),
								double: $t("iqrfnet.standard.light.errors.voltage.between"),
								required: $t("iqrfnet.standard.light.errors.voltage.required"),
							}'
						>
							<CInput
								v-model.number='voltage'
								type='number'
								min='0'
								max='10'
								step='.001'
								:label='$t("iqrfnet.standard.light.form.voltage")'
								:is-valid='touched ? valid : null'
								:invalid-feedback='errors.join(", ")'
							/>
						</ValidationProvider>
						<CButton
							class='mr-1'
							color='primary'
							:disabled='invalid'
							@click='setLai'
						>
							{{ $t('forms.set') }}
						</CButton>
						<CButton
							color='primary'
							:disabled='voltageResult === null'
							@click='showSetLaiResult'
						>
							{{ $t('forms.showLast') }}
						</CButton>
					</div>
				</CForm>
			</ValidationObserver>
		</CCardBody>
		<LdiCommandsResultModal
			ref='ldiCommandsResult'
			:result='answers'
		/>
		<SetVoltageResultModal
			ref='setLaiResult'
			:result='voltageResult'
		/>
	</CCard>
</template>

<script lang='ts'>
import {Component, Ref, Vue} from 'vue-property-decorator';
import {CButton, CCard, CCardBody, CCardFooter, CCardHeader, CForm, CInput, CSelect} from '@coreui/vue/src';
import {extend, ValidationObserver, ValidationProvider} from 'vee-validate';
import {FontAwesomeIcon} from '@fortawesome/vue-fontawesome';
import LdiCommandsResultModal from '@/components/IqrfNet/StandardManager/LdiCommandsResultModal.vue';
import SetVoltageResultModal from '@/components/IqrfNet/StandardManager/SetVoltageResultModal.vue';

import {between, double, integer, required} from 'vee-validate/dist/rules';
import DaemonMessageOptions from '@/ws/DaemonMessageOptions';

import StandardLightService from '@/services/DaemonApi/StandardLightService';

import { SetLaiResult, type LightAnswer, type LightAnswerResult} from '@/interfaces/DaemonApi/Standard';
import {MutationPayload} from 'vuex';
import { StandardLightDpaCommands } from '@/enums/IqrfNet/Standard';
import { IOption } from '@/interfaces/Coreui';

@Component({
	components: {
		CButton,
		CCard,
		CCardBody,
		CCardFooter,
		CCardHeader,
		CForm,
		CInput,
		CSelect,
		FontAwesomeIcon,
		LdiCommandsResultModal,
		SetVoltageResultModal,
		ValidationObserver,
		ValidationProvider
	},
	data: () => ({
		StandardLightDpaCommands,
	}),
})

/**
 * Light manager card for Standard Manager
 */
export default class LightManager extends Vue {

	/**
	 * @property {LdiCommandsResultModal} ldiCommandResults LDI commands result component
	 */
	@Ref('ldiCommandsResult') readonly ldiCommandResults!: LdiCommandsResultModal;

	/**
	 * @property {SetVoltageResultModal} setLaiResult Set LAI voltage result
	 */
	@Ref('setLaiResult') readonly setLaiResult!: SetVoltageResultModal;

	/**
	 * @var {string} msgId Daemon API msg ID
	 */
	private msgId = '';

	/**
	 * @var {number} address Device address
	 */
	private address = 1;

	/**
	 * @var {LightAnswerResult[]} answers Array of LDI standard answers
	 */
	private answers: LightAnswerResult[] = [];

	/**
	 * @var {number[]} commands Array of LDI commands to be sent
	 */
	private commands: number[] = [0];

	/**
	 * @var {StandardLightDpaCommands} dpaCommand DPA command
	 */
	private dpaCommand = StandardLightDpaCommands.SendLdi;

	/**
	 * @var {IOption[]} dpaCommandOptions DPA command options
	 */
	private dpaCommandOptions: IOption[] = [
		{
			label: this.$t('iqrfnet.standard.light.form.dpaCommand.options.send-ldi'),
			value: StandardLightDpaCommands.SendLdi,
		},
		{
			label: this.$t('iqrfnet.standard.light.form.dpaCommand.options.set-lai'),
			value: StandardLightDpaCommands.SetLai,
		}
	];

	/**
	 * @var {number} voltage Voltage to set
	 */
	private voltage: number = 0;

	/**
	 * @var {SetLaiResult | null} voltageResult Set voltage result
	 */
	private voltageResult: SetLaiResult|null = null;

	/**
	 * Component unsubscribe functin
	 */
	private unsubscribe: CallableFunction = () => {return;};

	/**
	 * Vue lifecycle hook created
	 */
	created(): void {
		extend('between', between);
		extend('double', double);
		extend('integer', integer);
		extend('required', required);
		this.unsubscribe = this.$store.subscribe((mutation: MutationPayload) => {
			if (mutation.type === 'daemonClient/SOCKET_ONSEND') {
				if (mutation.payload.mType === 'iqrfLight_SendLdiCommands') {
					this.answers = [];
				} else if (mutation.payload.mType === 'iqrfLight_SetLai') {
					this.voltageResult = null;
				}
				return;
			}
			if (mutation.type === 'daemonClient/SOCKET_ONMESSAGE') {
				if (mutation.payload.data.msgId !== this.msgId) {
					return;
				}
				this.$store.dispatch('spinner/hide');
				this.$store.dispatch('daemonClient/removeMessage', this.msgId);
				switch (mutation.payload.mType) {
					case 'iqrfLight_SendLdiCommands':
						this.handleSendLdiCommandsResponse(mutation.payload.data);
						break;
					case 'iqrfLight_SetLai':
						this.handleSetLaiResponse(mutation.payload.data);
						break;
					case 'messageError':
						this.$toast.error(
							this.$t('messageError', {error: mutation.payload.data.rsp.errorStr}).toString()
						);
						break;
				}
			}
		});
	}

	/**
	 * Vue lifecycle hook beforeDestroy
	 */
	beforeDestroy(): void {
		this.$store.dispatch('daemonClient/removeMessage', this.msgId);
		this.unsubscribe();
	}

	/**
	 * Creates a new LDI command field
	 */
	private addLdiCommand(): void {
		this.commands.push(0);
	}

	/**
	 * Removes a LDI command
	 */
	private removeLdiCommand(index: number): void {
		this.commands.splice(index, 1);
	}

	/**
	 * Sends LDI commands to device
	 */
	private sendLdiCommands(): void {
		this.$store.dispatch('spinner/show', {timeout: 30000});
		StandardLightService.sendLdiCommands(
			this.address,
			this.commands,
			new DaemonMessageOptions(null, 30000, 'iqrfnet.standard.light.messages.ldiCommand.timeout', () => this.msgId = '')
		)
			.then((msgId: string) => this.msgId = msgId);
	}

	/**
	 * Handles send LDI commands response
	 * @param response Daemon API response
	 */
	private handleSendLdiCommandsResponse(response): void {
		switch(response.status) {
			case 0: {
				const res = response.rsp.result.answers as LightAnswer[];
				const results: LightAnswerResult[] = [];
				for (let i = 0; i < res.length; ++i) {
					results.push({
						address: this.address,
						command: this.commands[i],
						...res[i],
					});
				}
				this.answers = results;
				this.showLdiCommandsResult();
				break;
			}
			case -1:
				this.$toast.error(
					this.$t('iqrfnet.standard.light.messages.ldiCommand.timeout').toString()
				);
				break;
			case 3:
				this.$toast.error(
					this.$t('iqrfnet.standard.light.messages.pnum').toString()
				);
				break;
			case 8:
				this.$toast.error(
					this.$t('forms.messages.noDevice', {address: this.address}).toString()
				);
				break;
			default:
				this.$toast.error(
					this.$t('iqrfnet.standard.light.messages.ldiCommand.failure').toString()
				);
				break;
		}

	}

	/**
	 * Show LDI commands result modal
	 */
	private showLdiCommandsResult(): void {
		this.ldiCommandResults.open();
	}

	/**
	 * Set LAI voltage
	 */
	private setLai(): void {
		this.$store.dispatch('spinner/show', {timeout: 30000});
		StandardLightService.setLaiVoltage(
			this.address,
			this.voltage,
			new DaemonMessageOptions(null, 30000, 'iqrf.standard.light.messages.voltage.timeout', () => this.msgId = ''),
		)
			.then((msgId: string) => this.msgId = msgId);
	}

	/**
	 * Handles set LAI voltage response
	 * @param response Daemon API response
	 */
	private handleSetLaiResponse(response): void {
		switch(response.status) {
			case 0:
				this.voltageResult = {
					address: this.address,
					currentVoltage: this.voltage,
					previousVoltage: response.rsp.result.prevVoltage,
				};
				this.showSetLaiResult();
				break;
			case -1:
				this.$toast.error(
					this.$t('iqrfnet.standard.light.messages.voltage.timeout').toString()
				);
				break;
			case 3:
				this.$toast.error(
					this.$t('iqrfnet.standard.light.messages.pnum').toString()
				);
				break;
			case 8:
				this.$toast.error(
					this.$t('forms.messages.noDevice', {address: this.address}).toString()
				);
				break;
			default:
				this.$toast.error(
					this.$t('iqrfnet.standard.light.messages.voltage.failure').toString()
				);
				break;
		}
	}

	/**
	 * Show set LAI voltage result modal
	 */
	private showSetLaiResult(): void {
		this.setLaiResult.open();
	}

}
</script>
