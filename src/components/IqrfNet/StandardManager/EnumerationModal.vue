<template>
	<CModal
		color='primary'
		:show.sync='show'
	>
		<template #header>
			<h5 class='modal-title'>
				{{ $t('iqrfnet.standard.enumeration.title') }}
			</h5>
		</template>
		<table class='modal-table'>
			<tr>
				<th>{{ $t('iqrfnet.standard.enumeration.step') }}</th>
				<td>{{ status.step }} / 8</td>
			</tr>
			<tr>
				<th>{{ $t('iqrfnet.standard.enumeration.time') }}</th>
				<td>{{ timeString }}</td>
			</tr>
		</table><hr>
		<div class='text-center'>
			<CProgress
				class='autonetwork-progress-bar'
				:color='status.color'
				:value='(status.step / 8) * 100'
				:animated='!status.finished'
			/>
			<div>{{ status.message }}</div>
		</div>
		<template #footer>
			<CButton
				color='primary'
				:disabled='!status.finished'
				@click='close()'
			>
				{{ $t('forms.close') }}
			</CButton>
		</template>
	</CModal>
</template>

<script lang='ts'>
import {Component} from 'vue-property-decorator';
import {CButton, CModal} from '@coreui/vue/src';
import ModalBase from '@/components/ModalBase.vue';

import DbService from '@/services/DaemonApi/DbService';
import Timer from '@/helpers/timer';

import { MutationPayload } from 'vuex';
import DaemonMessageOptions from '@/ws/DaemonMessageOptions';

@Component({
	components: {
		CButton,
		CModal,
	},
})
export default class EnumerationModal extends ModalBase {
	/**
	 * @var {string|null} msgId Daemon API message ID
	 */
	private msgId: string|null = null;

	/**
	 * @const defaultStatus Default enumeration status object
	 */
	private readonly defaultStatus = {
		step: 0,
		message: '',
		color: 'success',
		finished: false,
	};

	/**
	 * @var status Enumeration status object
	 */
	private status = {...this.defaultStatus};

	/**
	 * @var {Timer} timer Enumeration timer
	 */
	private timer = new Timer();

	/**
	 * Websocket store unsubscribe function
	 */
	private unsubscribe: CallableFunction = () => {return;};

	/**
	 * Converts timer from milliseconds to time string
	 */
	get timeString(): string {
		return new Date(this.timer.getTime()).toISOString().substr(11, 8);
	}

	created(): void {
		this.unsubscribe = this.$store.subscribe((mutation: MutationPayload) => {
			if (mutation.type === 'daemonClient/SOCKET_ONCLOSE') {
				this.$store.dispatch('daemonClient/removeMessage', this.msgId);
				this.status.color = 'error';
				this.status.message = this.$t('iqrfnet.standard.enumeration.messages.conn').toString();
				this.finish();
			}
			if (mutation.type !== 'daemonClient/SOCKET_ONMESSAGE' || mutation.payload.data.msgId !== this.msgId) {
				return;
			}
			if (mutation.payload.mType === 'messageError') {
				this.handleMessageError(mutation.payload.data);
			} else if (mutation.payload.mType === 'iqrfDb_Enumerate') {
				this.handleEnumerate(mutation.payload.data);
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
	 * Executes IqrfDb enumeration
	 */
	private enumerate(): void {
		this.status.finished = false;
		DbService.enumerate(new DaemonMessageOptions(null))
			.then((msgId: string) => this.msgId = msgId);
	}

	/**
	 * Handles enumerate response
	 * @param response Daemon API response
	 */
	private handleEnumerate(response): void {
		if (response.status !== 0) {
			this.$store.dispatch('daemonClient/removeMessage', this.msgId);
			this.finish();
			this.$toast.error(
				this.$t('iqrfnet.standard.enumeration.messages.failure').toString()
			);
			this.status.color = 'error';
			this.status.message = response.rsp.errorStr;
			return;
		}
		const status = response.rsp;
		this.status.step = status.step;
		this.status.message = status.stepStr;
		if (status.step === 8) {
			this.finish();
			this.$toast.success(
				this.$t('iqrfnet.standard.enumeration.messages.success').toString()
			);
		}
	}

	/**
	 * Handles message error response from Daemon API
	 * @param response Daemon API response
	 */
	private handleMessageError(response): void {
		this.$store.dispatch('daemonClient/removeMessage', this.msgId);
		this.$toast.error(
			this.$t('messageError', {error: response.rsp.errorStr}).toString()
		);
	}

	/**
	 * Opens modal window and starts enumeration process, with timer
	 */
	public start(): void {
		this.openModal();
		this.status = JSON.parse(JSON.stringify(this.defaultStatus));
		this.timer.start();
		this.enumerate();
	}

	/**
	 * Concludes enumeration, and stops timer
	 */
	private finish(): void {
		this.status.finished = true;
		this.timer.stop();
		this.$store.dispatch('daemonClient/removeMessage', this.msgId);
	}

	/**
	 * Closes modal and emit event to refresh device data
	 */
	private close(): void {
		this.closeModal();
		this.$emit('finished');
	}
}
</script>
