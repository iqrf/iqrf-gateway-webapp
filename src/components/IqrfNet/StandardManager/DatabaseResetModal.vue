<template>
	<CModal
		color='danger'
		:show.sync='show'
	>
		<template #header>
			<h5 class='modal-title'>
				{{ $t('iqrfnet.standard.modal.title') }}
			</h5>
		</template>
		{{ $t('iqrfnet.standard.modal.prompt') }}
		<template #footer>
			<CButton
				color='secondary'
				@click='closeModal'
			>
				{{ $t('forms.cancel') }}
			</CButton>
			<CButton
				color='danger'
				@click='resetDb'
			>
				{{ $t('iqrfnet.standard.table.actions.reset') }}
			</CButton>
		</template>
	</CModal>
</template>

<script lang='ts'>
import {Component} from 'vue-property-decorator';
import {CButton, CModal} from '@coreui/vue/src';
import ModalBase from '@/components/ModalBase.vue';

import DbService from '@/services/DaemonApi/DbService';

import {MutationPayload} from 'vuex';
import DaemonMessageOptions from '@/ws/DaemonMessageOptions';

/**
 * StandardDevices database reset dialog component
 */
@Component({
	components: {
		CButton,
		CModal,
	},
})
export default class DatabaseResetModal extends ModalBase {
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
			if (mutation.payload.mType === 'iqrfDb_Reset') {
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
	 * Resets the database
	 */
	private resetDb(): void {
		this.closeModal();
		this.$store.dispatch('spinner/show', {timeout: 10000});
		const options = new DaemonMessageOptions(null, 10000, 'iqrfnet.standard.table.messages.resetTimeout', () => this.msgId = '');
		DbService.resetDatabase(options)
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
		this.$emit('reset');
	}

	/**
	 * Open database reset modal
	 */
	public open(): void {
		this.openModal();
	}
}

</script>
