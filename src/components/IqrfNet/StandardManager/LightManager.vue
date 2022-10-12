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
						rules='required|integer|between:1,239'
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
						v-slot='{errors, touched, valid}'
						rules='required|integer|between:0,31'
						:custom-messages='{
							between: $t("iqrfnet.standard.light.form.messages.index"),
							integer: $t("forms.errors.integer"),
							required: $t("iqrfnet.standard.light.form.messages.index"),
						}'
					>
						<CInput
							v-model.number='index'
							type='number'
							min='0'
							max='31'
							:label='$t("iqrfnet.standard.light.form.index")'
							:is-valid='touched ? valid : null'
							:invalid-feedback='errors.join(", ")'
						/>
					</ValidationProvider>
					<ValidationProvider
						v-slot='{errors, touched, valid}'
						rules='required|integer|between:0,100'
						:custom-messages='{
							between: $t("iqrfnet.standard.light.form.messages.power"),
							integer: $t("forms.errors.integer"),
							required: $t("iqrfnet.standard.light.form.messages.power"),
						}'
					>
						<CInput
							v-model.number='power'
							type='number'
							min='0'
							max='100'
							:label='$t("iqrfnet.standard.light.form.power")'
							:is-valid='touched ? valid : null'
							:invalid-feedback='errors.join(", ")'
						/>
					</ValidationProvider>
					<CButton
						class='mr-1'
						color='primary'
						:disabled='invalid'
						@click.prevent='enumerate'
					>
						{{ $t('forms.enumerate') }}
					</CButton>
					<CButton
						class='mr-1'
						color='primary'
						:disabled='invalid'
						@click.prevent='getPower'
					>
						{{ $t('iqrfnet.standard.light.form.getPower') }}
					</CButton>
					<CButton
						class='mr-1'
						color='primary'
						:disabled='invalid'
						@click.prevent='setPower'
					>
						{{ $t('iqrfnet.standard.light.form.setPower') }}
					</CButton>
					<CButton
						class='mr-1'
						color='primary'
						:disabled='invalid'
						@click.prevent='incrementPower'
					>
						{{ $t('iqrfnet.standard.light.form.increment') }}
					</CButton>
					<CButton
						color='primary'
						:disabled='invalid'
						@click.prevent='decrementPower'
					>
						{{ $t('iqrfnet.standard.light.form.decrement') }}
					</CButton>
				</CForm>
			</ValidationObserver>
		</CCardBody>
		<CCardFooter v-if='responseType !== StandardResponses.NONE'>
			<table class='table'>
				<thead>
					{{ $t(`iqrfnet.standard.light.${responseType === StandardResponses.ENUMERATE ? 'enum' : 'powerInfo'}`) }}
				</thead>
				<tbody v-if='responseType === StandardResponses.ENUMERATE'>
					<tr>
						<th>{{ $t('iqrfnet.standard.light.lights') }}</th>
						<td>{{ numLights }}</td>
					</tr>
				</tbody>
				<tbody v-else>
					<tr>
						<th>{{ $t('iqrfnet.standard.light.index') }}</th>
						<td>{{ responseIndex }}</td>
					</tr>
					<tr>
						<th>{{ $t('iqrfnet.standard.light.power') }}</th>
						<td>{{ prevPower }}</td>
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

import {between, integer, required} from 'vee-validate/dist/rules';
import {StandardResponses} from '@/enums/IqrfNet/Standard';
import DaemonMessageOptions from '@/ws/DaemonMessageOptions';

import StandardLightService, {StandardLight} from '@/services/DaemonApi/StandardLightService';

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
		ValidationObserver,
		ValidationProvider
	},
	data: () =>  ({
		StandardResponses,
	}),
})

/**
 * Light manager card for Standard Manager
 */
export default class LightManager extends Vue {
	/**
	 * @var {string} msgId Daemon API message ID
	 */
	private msgId = '';

	/**
	 * @var {number} address Address of device implementing the light standard
	 */
	private address = 1;

	/**
	 * @var {number} index Index of light to manage
	 */
	private index = 0;

	/**
	 * @var {number} responseIndex Index of light in responses
	 */
	private responseIndex = 0;

	/**
	 * @var {number} numLights Number of lights implemented by the device
	 */
	private numLights = 0;

	/**
	 * @var {number} power Power to set the light to
	 */
	private power = 0;

	/**
	 * @var {number} prevPower Previous power setting of the light
	 */
	private prevPower = 0;

	/**
	 * @var {StandardResponses} responseType Type of Light standard message
	 */
	private responseType: StandardResponses = StandardResponses.NONE;

	/**
	 * Component unsubscribe function
	 */
	private unsubscribe: CallableFunction = () => {return;};

	/**
	 * vue lifecycle hook created
	 */
	created(): void {
		extend('between', between);
		extend('integer', integer);
		extend('required', required);
		this.unsubscribe = this.$store.subscribe((mutation: MutationPayload) => {
			if (mutation.type === 'daemonClient/SOCKET_ONSEND') {
				this.responseType = StandardResponses.NONE;
				this.responseIndex = this.index;
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
				} else if (mutation.payload.mType === 'iqrfLight_Enumerate') {
					this.handleEnumerateResponse(mutation.payload);
				} else if (['iqrfLight_SetPower', 'iqrfLight_IncrementPower', 'iqrfLight_DecrementPower'].includes(mutation.payload.mType)) {
					this.handlePowerResponse(mutation.payload);
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
	 * Creates WebSocket request options object
	 * @returns {DaemonMessageOptions} WebSocket request options
	 */
	private buildOptions(): DaemonMessageOptions {
		return new DaemonMessageOptions(null, 30000, 'iqrfnet.standard.light.messages.timeout', () => this.msgId = '');
	}

	/**
	 * Performs Light standard enumeration on a device
	 */
	private enumerate(): void {
		this.$store.dispatch('spinner/show', {timeout: 30000});
		StandardLightService.enumerate(this.address, this.buildOptions())
			.then((msgId: string) => this.msgId = msgId);
	}

	/**
	 * Handles Enumerate response
	 * @param response Daemon API response
	 */
	private handleEnumerateResponse(response): void {
		if (response.data.status === 0) {
			this.numLights = response.data.rsp.result.lights;
			this.$toast.success(
				this.$t('iqrfnet.standard.light.messages.success').toString()
			);
			this.responseType = StandardResponses.ENUMERATE;
		} else {
			this.handleError(response);
		}
	}

	/**
	 * Retrieves power setting of a light
	 */
	private getPower(): void {
		this.$store.dispatch('spinner/show', {timeout: 30000});
		StandardLightService.getPower(this.address, this.index, this.buildOptions())
			.then((msgId: string) => this.msgId = msgId);
	}

	/**
	 * Changes power setting of a light to a specific value
	 */
	private setPower(): void {
		this.$store.dispatch('spinner/show', {timeout: 30000});
		StandardLightService.setPower(this.address, [new StandardLight(this.index, this.power)], this.buildOptions())
			.then((msgId: string) => this.msgId = msgId);
	}

	/**
	 * Increments light power according to the Light standard
	 */
	private incrementPower(): void {
		this.$store.dispatch('spinner/show', {timeout: 30000});
		StandardLightService.incrementPower(this.address, [new StandardLight(this.index, this.power)], this.buildOptions())
			.then((msgId: string) => this.msgId = msgId);
	}

	/**
	 * Decrements light power according to the Light standard
	 */
	private decrementPower(): void {
		this.$store.dispatch('spinner/show', {timeout: 30000});
		StandardLightService.decrementPower(this.address, [new StandardLight(this.index, this.power)], this.buildOptions())
			.then((msgId: string) => this.msgId = msgId);
	}

	/**
	 * Handles power response
	 * @param response Daemon API response
	 */
	private handlePowerResponse(response): void {
		if (response.data.status === 0) {
			this.prevPower = response.data.rsp.result.prevVals[0];
			this.$toast.success(
				this.$t('iqrfnet.standard.light.messages.success').toString()
			);
			this.responseType = StandardResponses.READ;
		} else {
			this.handleError(response);
		}
	}

	/**
	 * Handles error response
	 * @param response Daemon API response
	 */
	private handleError(response): void {
		switch(response.data.status) {
			case -1:
				this.$toast.error(
					this.$t('iqrfnet.standard.light.messages.timeout').toString()
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
					this.$t('iqrfnet.standard.light.messages.failure').toString()
				);
				break;
		}
	}
}
</script>
