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
	<CModal
		:show.sync='modalState'
		color='warning'
	>
		<template #header>
			<h5 class='modal-title'>
				{{ $t('daemonStatus.modalTitle') }}
			</h5>
		</template>
		{{ $t('daemonStatus.modalPrompt') }}
		<template #footer>
			<CButton
				color='warning'
				@click='hideModal()'
			>
				{{ $t('forms.close') }}
			</CButton>
		</template>
	</CModal>
</template>

<script lang='ts'>
import {Component, Vue} from 'vue-property-decorator';
import {CButton, CModal} from '@coreui/vue/src';

import {IMonitorMsg} from '@/interfaces/DaemonApi/Monitor';
import {mapGetters, MutationPayload} from 'vuex';

@Component({
	components: {
		CButton,
		CModal,
	},
	computed: {
		...mapGetters({
			modalState: 'monitorClient/getModalState'
		}),
	},
})

/**
 * Daemon mode notice modal window
 */
export default class DaemonModeModal extends Vue {

	/**
	 * Component unsubscribe function
	 */
	private unsubscribe: CallableFunction = () => {return;};

	/**
	 * Subscribes to Monitor vuex mutations
	 */
	created(): void {
		this.unsubscribe = this.$store.subscribe((mutation: MutationPayload) => {
			if (mutation.type === 'monitorClient/SOCKET_ONMESSAGE') {
				this.handleMonitorMessage(mutation.payload);
			}
		});
	}

	/**
	 * Parses monitor message and applies changes to internal state
	 * @param {IMonitorMsg} message Monitor message object
	 */
	private handleMonitorMessage(message: IMonitorMsg): void {
		const mode = message.data.operMode;
		if (mode !== this.$store.getters['monitorClient/getMode']) {
			this.$store.commit('monitorClient/SET_MODE', mode);
		}
		this.$store.commit('monitorClient/UPDATE_QUEUE', message.data.msgQueueLen);
	}

	/**
	 * Requests to hide modal window
	 */
	private hideModal(): void {
		this.$store.commit('monitorClient/HIDE_MODAL');
	}
}
</script>
