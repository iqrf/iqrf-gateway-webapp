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
	<CCard class='border-0 card-margin-bottom'>
		<CCardBody>
			<ValidationObserver v-slot='{invalid}'>
				<CForm>
					<ValidationProvider
						v-slot='{errors, touched, valid}'
						rules='integer|required|between:1,239'
						:custom-messages='{
							between: $t("iqrfnet.standard.form.messages.address"),
							integer: $t("forms.errors.integer"),
							required: $t("iqrfnet.standard.form.messages.address"),
						}'
					>
						<CInput
							v-model.number='address'
							type='number'
							min='1'
							max='239'
							:label='$t("iqrfnet.standard.form.address")'
							:is-valid='touched ? valid : null'
							:invalid-feedback='errors.join(", ")'
						/>
					</ValidationProvider>
					<ValidationProvider
						v-for='i of commands.length'
						:key='i'
						v-slot='{errors, touched, valid}'
						rules='integer|required|between:0,65535'
						:custom-messages='{
							between: $t("iqrfnet.standard.dali.form.messages.command"),
							integer: $t("forms.errors.integer"),
							required: $t("iqrfnet.standard.dali.form.messages.command"),
						}'
					>
						<CInput
							v-model.number='commands[i-1]'
							type='number'
							min='0'
							max='65535'
							:label='$t("iqrfnet.standard.dali.form.command")'
							:is-valid='touched ? valid : null'
							:invalid-feedback='errors.join(", ")'
						>
							<template #prepend-content>
								<span
									v-if='i === commands.length'
									class='text-success'
									@click='addDaliCommand'
								>
									<FontAwesomeIcon :icon='["far", "square-plus"]' size='xl' />
								</span>
							</template>
							<template #append-content>
								<span
									v-if='commands.length > 1'
									class='text-danger'
									@click='removeDaliCommand(i-1)'
								>
									<FontAwesomeIcon :icon='["far", "trash-alt"]' size='lg' />
								</span>
							</template>
						</CInput>
					</ValidationProvider>
					<CButton
						color='primary'
						:disabled='invalid'
						@click='sendDali'
					>
						{{ $t('iqrfnet.standard.dali.form.sendCommand') }}
					</CButton>
				</CForm>
			</ValidationObserver>
		</CCardBody>
		<CCardFooter v-if='answers.length > 0'>
			<table class='table'>
				<thead>
					{{ $t('iqrfnet.standard.dali.answers') }}
				</thead>
				<tbody class='text-center'>
					<tr>
						<th>{{ $t('iqrfnet.standard.dali.status') }}</th>
						<th>{{ $t('iqrfnet.standard.dali.value') }}</th>
					</tr>
					<tr v-for='(answer, i) of answers' :key='i'>
						<td>{{ answer.status }}</td>
						<td>{{ answer.value }}</td>
					</tr>
				</tbody>
			</table>
		</CCardFooter>
	</CCard>
</template>

<script lang='ts'>
import {Component, Vue} from 'vue-property-decorator';
import {CButton, CCard, CCardBody, CCardFooter, CCardHeader, CForm, CInput} from '@coreui/vue/src';
import {extend, ValidationObserver, ValidationProvider} from 'vee-validate';
import {FontAwesomeIcon} from '@fortawesome/vue-fontawesome';

import {between, integer, required} from 'vee-validate/dist/rules';
import DaemonMessageOptions from '@/ws/DaemonMessageOptions';

import StandardDaliService from '@/services/DaemonApi/StandardDaliService';

import {IDaliAnswer} from '@/interfaces/standard';
import {MutationPayload} from 'vuex';

@Component({
	components: {
		CButton,
		CCard,
		CCardBody,
		CCardFooter,
		CCardHeader,
		CForm,
		CInput,
		FontAwesomeIcon,
		ValidationObserver,
		ValidationProvider
	}
})

/**
 * Dali manager card for Standard Manager
 */
export default class DaliManager extends Vue {
	/**
	 * @var {string} msgId Daemon API msg ID
	 */
	private msgId = '';

	/**
	 * @var {number} address Address of device implementing DALI standard
	 */
	private address = 1;

	/**
	 * @var {Array<IDaliAnswer>} answers Array of DALI standard answers
	 */
	private answers: Array<IDaliAnswer> = [];

	/**
	 * @var {Array<number>} commands Array of DALI commands to be sent
	 */
	private commands: Array<number> = [0];

	/**
	 * Component unsubscribe functin
	 */
	private unsubscribe: CallableFunction = () => {return;};

	/**
	 * Vue lifecycle hook created
	 */
	created(): void {
		extend('between', between);
		extend('integer', integer);
		extend('required', required);
		this.unsubscribe = this.$store.subscribe((mutation: MutationPayload) => {
			if (mutation.type === 'daemonClient/SOCKET_ONSEND') {
				this.answers = [];
				return;
			}
			if (mutation.type === 'daemonClient/SOCKET_ONMESSAGE') {
				if (mutation.payload.data.msgId !== this.msgId) {
					return;
				}
				this.$store.dispatch('spinner/hide');
				this.$store.dispatch('daemonClient/removeMessage', this.msgId);
				if (mutation.payload.mType === 'messageError') {
					this.$toast.error(
						this.$t('messageError', {error: mutation.payload.data.rsp.errorStr}).toString()
					);
				} else {
					this.handleResponse(mutation.payload);
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
	 * Creates a new DALI command field
	 */
	private addDaliCommand(): void {
		this.commands.push(0);
	}

	/**
	 * Removes a DALI command
	 */
	private removeDaliCommand(index: number): void {
		this.commands.splice(index, 1);
	}

	/**
	 * Sends DALI commands to device
	 */
	private sendDali(): void {
		this.$store.dispatch('spinner/show', {timeout: 30000});
		StandardDaliService.send(this.address, this.commands, new DaemonMessageOptions(null, 30000, 'iqrfnet.standard.dali.messages.timeout', () => this.msgId = ''))
			.then((msgId: string) => this.msgId = msgId);
	}

	/**
	 * Handles DALI response
	 * @param response Daemon API response
	 */
	private handleResponse(response): void {
		if (response.data.status === 0) {
			this.$toast.success(
				this.$t('iqrfnet.standard.dali.messages.success').toString()
			);
			this.answers = response.data.rsp.result.answers;
		} else {
			this.handleError(response);
		}
	}

	/**
	 * Handles DALI errors
	 * @param response Daemon API response
	 */
	private handleError(response): void {
		switch(response.data.status) {
			case -1:
				this.$toast.error(
					this.$t('iqrfnet.standard.dali.messages.timeout').toString()
				);
				break;
			case 3:
				this.$toast.error(
					this.$t('iqrfnet.standard.dali.messages.pnum').toString()
				);
				break;
			case 8:
				this.$toast.error(
					this.$t('forms.messages.noDevice', {address: this.address}).toString()
				);
				break;
			default:
				this.$toast.error(
					this.$t('iqrfnet.standard.dali.messages.failure').toString()
				);
				break;
		}
	}
}
</script>
