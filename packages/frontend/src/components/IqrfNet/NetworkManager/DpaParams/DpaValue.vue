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
	<v-card flat tile>
		<v-card-title>{{ $t('iqrfnet.networkManager.dpaParams.dpaValue.title') }}</v-card-title>
		<v-card-text>
			<v-form>
				<v-select
					v-model='type'
					:items='options'
					:label='$t("iqrfnet.networkManager.dpaParams.dpaValue.type")'
					:hint='$t("iqrfnet.networkManager.dpaParams.dpaValue.notes.value")'
					persistent-hint
					class='mb-2'
				/>
				<v-btn
					class='mr-1'
					color='primary'
					@click='getValue'
				>
					{{ $t('forms.get') }}
				</v-btn>
				<v-btn
					color='primary'
					@click='setValue'
				>
					{{ $t('forms.set') }}
				</v-btn>
			</v-form>
		</v-card-text>
	</v-card>
</template>

<script lang='ts'>
import {Component, Vue} from 'vue-property-decorator';

import {DpaParamAction, DpaValueType} from '@/enums/IqrfNet/DpaParams';
import DaemonMessageOptions from '@/ws/DaemonMessageOptions';

import IqmeshNetworkService from '@/services/DaemonApi/IqmeshNetworkService';

import {ISelectItem} from '@/interfaces/Vuetify';
import {MutationPayload} from 'vuex';

/**
 * DPA params DPA value component
 */
@Component
export default class DpaValue extends Vue {
	/**
	 * @var {string} msgId Daemon API msg ID
	 */
	private msgId = '';

	/**
	 * @var {number} type DPA value type option
	 */
	private type = DpaValueType.RSSI;

	/**
	 * @constant {Array<ISelectItem>} options DPA value type options
	 */
	private options: Array<ISelectItem> = [
		{
			text: this.$t('iqrfnet.networkManager.dpaParams.dpaValue.types.rssi').toString(),
			value: DpaValueType.RSSI,
		},
		{
			text: this.$t('iqrfnet.networkManager.dpaParams.dpaValue.types.voltage').toString(),
			value: DpaValueType.SUPPLY_VOLTAGE,
		},
		{
			text: this.$t('iqrfnet.networkManager.dpaParams.dpaValue.types.system').toString(),
			value: DpaValueType.SYSTEM,
		},
		{
			text: this.$t('iqrfnet.networkManager.dpaParams.dpaValue.types.user').toString(),
			value: DpaValueType.USER,
		},
	];

	/**
	 * Websocket mutation handler
	 */
	private unsubscribe: CallableFunction = () => {return;};

	/**
	 * Registers mutation handling
	 */
	created(): void {
		this.unsubscribe = this.$store.subscribe((mutation: MutationPayload) => {
			if (mutation.type !== 'daemonClient/SOCKET_ONMESSAGE') {
				return;
			}
			if (mutation.payload.data.msgId !== this.msgId) {
				return;
			}
			this.$store.dispatch('daemonClient/removeMessage', this.msgId);
			this.$store.dispatch('spinner/hide');
			if (mutation.payload.mType === 'iqmeshNetwork_DpaValue') {
				this.handleValue(mutation.payload.data);
			} else {
				this.$toast.error(
					this.$t('iqrfnet.messages.genericError').toString()
				);
			}
		});
	}

	/**
	 * Unregister mutation handling
	 */
	beforeDestroy(): void {
		this.$store.dispatch('daemonClient/removeMessage', this.msgId);
		this.unsubscribe();
	}

	/**
	 * Retrieves DPA value type
	 */
	private getValue(): void {
		this.$store.dispatch('spinner/show', {
			timeout: 5000,
			text: this.$t('iqrfnet.networkManager.dpaParams.dpaValue.messages.get').toString()
		});
		const options = new DaemonMessageOptions(null, 5000, 'iqrfnet.networkManager.dpaParams.dpaValue.messages.timeout', () => this.msgId = '');
		IqmeshNetworkService.dpaValue(DpaParamAction.GET, null, options)
			.then((msgId: string) => this.msgId = msgId);
	}

	/**
	 * Sets DPA value type
	 */
	private setValue(): void {
		this.$store.dispatch('spinner/show', {
			timeout: 5000,
			text: this.$t('iqrfnet.networkManager.dpaParams.dpaValue.messages.set').toString()
		});
		const options = new DaemonMessageOptions(null, 5000, 'iqrfnet.networkManager.dpaParams.dpaValue.messages.timeout', () => this.msgId = '');
		IqmeshNetworkService.dpaValue(DpaParamAction.SET, this.type, options)
			.then((msgId: string) => this.msgId = msgId);
	}

	/**
	 * Handles DpaValue response
	 * @param response Response
	 */
	private handleValue(response): void {
		if (response.status === 0) {
			const action = response.rsp.action;
			if (action === DpaParamAction.GET) {
				this.type = response.rsp.type;
			}
			this.$toast.success(
				this.$t(
					'iqrfnet.networkManager.dpaParams.dpaValue.messages.' + (action === DpaParamAction.GET ? 'get' : 'set') + 'Success'
				).toString()
			);
			return;
		}
		if (response.rsp.action !== undefined) {
			const action = response.rsp.action;
			this.$toast.error(
				this.$t(
					'iqrfnet.networkManager.dpaParams.dpaValue.messages.' + (action === DpaParamAction.GET ? 'get' : 'set') + 'Failed',
					{error: response.statusStr},
				).toString()
			);
			return;
		}
		this.$toast.error(
			this.$t(
				'iqrfnet.networkManager.dpaParams.dpaValue.messages.genericError',
				{error: response.statusStr},
			).toString()
		);
	}
}
</script>
