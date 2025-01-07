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
		<v-card-title>{{ $t('iqrfnet.networkManager.dpaParams.frcParams.title') }}</v-card-title>
		<v-card-text>
			<v-form>
				<v-select
					v-model='responseTime'
					:items='responseTimeOptions'
					:label='$t("iqrfnet.networkManager.dpaParams.frcParams.responseTime")'
					:hint='$t("iqrfnet.networkManager.dpaParams.frcParams.notes.responseTime")'
					persistent-hint
				/>
				<v-checkbox
					v-model='offlineFrc'
					class='mb-2'
					:label='$t("iqrfnet.networkManager.dpaParams.frcParams.offlineFrc")'
					:hint='$t("iqrfnet.networkManager.dpaParams.frcParams.notes.offlineFrc")'
					persistent-hint
					dense
				/>
				<v-btn
					class='mr-1'
					color='primary'
					@click='getFrcParams'
				>
					{{ $t('forms.get') }}
				</v-btn>
				<v-btn
					color='primary'
					@click='setFrcParams'
				>
					{{ $t('forms.set') }}
				</v-btn>
			</v-form>
		</v-card-text>
	</v-card>
</template>

<script lang='ts'>
import {Component, Vue} from 'vue-property-decorator';

import {DpaParamAction, FrcResponseTime} from '@/enums/IqrfNet/DpaParams';
import DaemonMessageOptions from '@/ws/DaemonMessageOptions';

import IqmeshNetworkService from '@/services/DaemonApi/IqmeshNetworkService';

import {ISelectItem} from '@/interfaces/Vuetify';
import {MutationPayload} from 'vuex';

/**
 * DPA params FRC params component
 */
@Component
export default class FrcParams extends Vue {
	/**
	 * @var {string} msgId Daemon API msg ID
	 */
	private msgId = '';

	/**
	 * @var {FrcResponseTime} responseTime FRC response time
	 */
	private responseTime: FrcResponseTime = FrcResponseTime.MS40;

	/**
	 * @var {boolean} offlineFrc Offline FRC
	 */
	private offlineFrc = false;

	/**
	 * Websocket mutation handler
	 */
	private unsubscribe: CallableFunction = () => {return;};

	/**
	 * Generates FRC response time options for select component
	 * @return {Array<ISelectItem>} FRC response time select options
	 */
	get responseTimeOptions(): Array<ISelectItem> {
		const options: Array<ISelectItem> = [];
		Object.values(FrcResponseTime).filter((v): v is number => Number.isInteger(v))
			.forEach((item: number) => {
				options.push({
					text: this.$t('iqrfnet.networkManager.dpaParams.frcParams.responseTimes.' + item).toString(),
					value: item,
				});
			});
		return options;
	}

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
			if (mutation.payload.mType === 'iqmeshNetwork_FrcParams') {
				this.handleFrcParams(mutation.payload.data);
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
	 * Retrieves FRC params
	 */
	private getFrcParams(): void {
		this.$store.dispatch('spinner/show', {
			timeout: 5000,
			text: this.$t('iqrfnet.networkManager.dpaParams.frcParams.messages.get').toString()
		});
		const options = new DaemonMessageOptions(null, 5000, 'iqrfnet.networkManager.dpaParams.frcParams.messages.timeout', () => this.msgId = '');
		IqmeshNetworkService.frcParams(DpaParamAction.GET, null, null, options)
			.then((msgId: string) => this.msgId = msgId);
	}

	/**
	 * Sets FRC params
	 */
	private setFrcParams(): void {
		this.$store.dispatch('spinner/show', {
			timeout: 5000,
			text: this.$t('iqrfnet.networkManager.dpaParams.frcParams.messages.set').toString()
		});
		const options = new DaemonMessageOptions(null, 5000, 'iqrfnet.networkManager.dpaParams.frcParams.messages.timeout', () => this.msgId = '');
		IqmeshNetworkService.frcParams(DpaParamAction.SET, this.responseTime, this.offlineFrc, options)
			.then((msgId: string) => this.msgId = msgId);
	}

	/**
	 * Handles FrcParams response
	 * @param response Response
	 */
	private handleFrcParams(response): void {
		if (response.status === 0) {
			const action = response.rsp.action;
			if (action === DpaParamAction.GET) {
				this.responseTime = response.rsp.responseTime;
				this.offlineFrc = response.rsp.offlineFrc;
			}
			this.$toast.success(
				this.$t(
					'iqrfnet.networkManager.dpaParams.frcParams.messages.' + (action === DpaParamAction.GET ? 'get' : 'set') + 'Success'
				).toString()
			);
			return;
		}
		if (response.rsp.action !== undefined) {
			const action = response.rsp.action;
			this.$toast.error(
				this.$t(
					'iqrfnet.networkManager.dpaParams.frcParams.messages.' + (action === DpaParamAction.GET ? 'get' : 'set') + 'Failed',
					{error: response.statusStr},
				).toString()
			);
			return;
		}
		this.$toast.error(
			this.$t(
				'iqrfnet.networkManager.dpaParams.frcParams.messages.genericError',
				{error: response.statusStr},
			).toString()
		);
	}
}
</script>
