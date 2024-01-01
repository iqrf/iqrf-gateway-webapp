<!--
Copyright 2017-2024 IQRF Tech s.r.o.
Copyright 2019-2024 MICRORISC s.r.o.

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
	<v-card flat tile>
		<v-card-title>{{ $t('iqrfnet.networkManager.discovery.title') }}</v-card-title>
		<v-card-text>
			<ValidationObserver v-slot='{invalid}'>
				<v-form>
					<ValidationProvider
						v-slot='{errors, touched, valid}'
						rules='integer|required|between:0,7'
						:custom-messages='{
							integer: $t("forms.errors.integer"),
							required: $t("iqrfnet.networkManager.discovery.errors.txPower"),
							between: $t("iqrfnet.networkManager.discovery.errors.txPower"),
						}'
					>
						<v-text-field
							v-model.number='txPower'
							type='number'
							min='0'
							max='7'
							:label='$t("iqrfnet.networkManager.discovery.form.txPower")'
							:success='touched ? valid : null'
							:error-messages='errors'
						/>
					</ValidationProvider>
					<ValidationProvider
						v-slot='{errors, touched, valid}'
						rules='integer|required|between:0,239'
						:custom-messages='{
							integer: $t("forms.errors.integer"),
							required: $t("iqrfnet.networkManager.discovery.errors.maxAddr"),
							between: $t("iqrfnet.networkManager.discovery.errors.maxAddr")
						}'
					>
						<v-text-field
							v-model.number='maxAddr'
							type='number'
							min='0'
							max='239'
							:label='$t("iqrfnet.networkManager.discovery.form.maxAddr")'
							:success='touched ? valid : null'
							:error-messages='errors'
						/>
					</ValidationProvider>
					<v-btn
						color='primary'
						:disabled='invalid'
						@click='runDiscovery'
					>
						{{ $t("forms.runDiscovery") }}
					</v-btn>
				</v-form>
			</ValidationObserver>
		</v-card-text>
	</v-card>
</template>

<script lang='ts'>
import {Component, Vue} from 'vue-property-decorator';
import {extend, ValidationObserver, ValidationProvider} from 'vee-validate';

import {between, integer, required} from 'vee-validate/dist/rules';
import IqrfNetService from '@/services/IqrfNetService';

import {MutationPayload} from 'vuex';
import DaemonMessageOptions from '@/ws/DaemonMessageOptions';

@Component({
	components: {
		ValidationObserver,
		ValidationProvider
	}
})

/**
 * Discovery manager card for Network Manager
 */
export default class DiscoveryManager extends Vue {
	/**
	 * @var {number} maxAddr Maximum node address
	 */
	private maxAddr = 239;

	/**
	 * @var {string|null} msgId Daemon api message id
	 */
	private msgId: string|null = null;

	/**
	 * @var {number} txPower Discovery call TX power
	 */
	private txPower = 6;

	/**
	 * Component unsubscribe function
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
			if (mutation.type === 'daemonClient/SOCKET_ONMESSAGE') {
				if (mutation.payload.data.msgId !== this.msgId) {
					return;
				}
				this.$store.commit('spinner/HIDE');
				this.$store.dispatch('daemonClient/removeMessage', this.msgId);
				if (mutation.payload.mType === 'iqrfEmbedCoordinator_Discovery') {
					this.handleDiscoveryResponse(mutation.payload);
				} else if (mutation.payload.mType === 'messageError') {
					this.$toast.error(
						this.$t('messageError', {error: mutation.payload.data.rsp.errorStr}).toString()
					);
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
	 * Performs Discovery Daemon API call
	 */
	private runDiscovery(): void {
		this.$store.commit('spinner/SHOW');
		this.$store.commit('spinner/UPDATE_TEXT', this.$t('iqrfnet.networkManager.discovery.messages.spinnerNote').toString());
		IqrfNetService.discovery(this.txPower, this.maxAddr, new DaemonMessageOptions(null))
			.then((msgId: string) => this.msgId = msgId);
	}

	/**
	 * Handles Discovery Daemon API call response
	 */
	private handleDiscoveryResponse(response): void {
		if (response.data.status === 0) {
			this.$emit('update-devices', {
				message: this.$t('iqrfnet.networkManager.discovery.messages.success').toString(),
				type: 'success',
			});
		} else if (response.data.status === -1) {
			this.$toast.error(
				this.$t('iqrfnet.networkManager.discovery.messages.timeout').toString()
			);
		} else {
			this.$toast.error(
				this.$t('iqrfnet.networkManager.discovery.messages.genericError').toString()
			);
		}
	}
}
</script>
