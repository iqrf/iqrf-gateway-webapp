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
	<v-dialog
		v-model='show'
		width='50%'
		persistent
		no-click-animation
	>
		<template #activator='{attrs, on}'>
			<v-btn
				color='error'
				small
				v-bind='attrs'
				v-on='on'
				@click='openModal'
			>
				<v-icon small>
					mdi-refresh
				</v-icon>
				{{ $t('iqrfnet.standard.table.actions.reset') }}
			</v-btn>
		</template>
		<v-card>
			<v-card-title>
				{{ $t('iqrfnet.standard.modal.title') }}
			</v-card-title>
			<v-card-text>
				{{ $t('iqrfnet.standard.modal.prompt') }}
			</v-card-text>
			<v-card-actions>
				<v-spacer />
				<v-btn
					@click='closeModal'
				>
					{{ $t('forms.cancel') }}
				</v-btn>
				<v-btn
					color='error'
					@click='resetDb'
				>
					{{ $t('iqrfnet.standard.table.actions.reset') }}
				</v-btn>
			</v-card-actions>
		</v-card>
	</v-dialog>
</template>

<script lang='ts'>
import {Component} from 'vue-property-decorator';
import ModalBase from '@/components/ModalBase.vue';

import InfoService from '@/services/DaemonApi/InfoService';

import {MutationPayload} from 'vuex';
import DaemonMessageOptions from '@/ws/DaemonMessageOptions';

/**
 * StandardDevices database reset dialog component
 */
@Component
export default class DatabaseResetDialog extends ModalBase {
	/**
	 * @var {string} msgId Daemon API message ID
	 */
	private msgId = '';

	/**
	 * Websocket store unsubscribe function
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
			if (mutation.payload.mType === 'infoDaemon_Reset') {
				this.handleReset(mutation.payload.data);
			}
		});
	}

	/**
	 * Unregisters mutation handling
	 */
	beforeDestroy(): void {
		this.$store.dispatch('daemonClient/removeMessage', this.msgId);
		this.unsubscribe();
	}

	/**
	 * Resets the IqrfInfo database
	 */
	private resetDb(): void {
		this.$store.dispatch('spinner/show', {timeout: 10000});
		const options = new DaemonMessageOptions(null, 10000, this.$t('iqrfnet.standard.table.messages.resetTimeout'), () => this.msgId = '');
		InfoService.reset(true, options)
			.then((msgId: string) => this.msgId = msgId);
	}

	/**
	 * Handles Reset Daemon API response
	 * @param response Daemon API response
	 */
	private handleReset(response): void {
		if (response.status !== 0) {
			this.$toast.error(
				this.$t('iqrfnet.standard.table.messages.resetFailed', {error: response.rsp.errorStr}).toString()
			);
			return;
		}
		this.$toast.success(
			this.$t('iqrfnet.standard.table.messages.resetSuccess').toString()
		);
		this.closeModal();
		this.$emit('reset');
	}
}
</script>
