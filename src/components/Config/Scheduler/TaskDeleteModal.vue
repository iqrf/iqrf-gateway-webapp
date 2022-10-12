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
	<span>
		<CButton
			color='danger'
			size='sm'
			@click='openModal'
		>
			<CIcon :content='cilTrash' size='sm' />
			{{ $t('table.actions.delete') }}
		</CButton>
		<CModal
			:show.sync='show'
			color='danger'
			size='lg'
			:close-on-backdrop='false'
			:fade='false'
		>
			<template #header>
				<h5 class='modal-title'>
					{{ $t('config.daemon.scheduler.modal.title') }}
				</h5>
			</template>
			{{ $t('config.daemon.scheduler.modal.deletePrompt', {task: syncTaskId}) }}
			<template #footer>
				<CButton
					color='secondary'
					@click='closeModal'
				>
					{{ $t('forms.cancel') }}
				</CButton>
				<CButton
					color='danger'
					@click='removeTask'
				>
					{{ $t('table.actions.deleteAll') }}
				</CButton>
			</template>
		</CModal>
	</span>
</template>

<script lang='ts'>
import {Component, PropSync} from 'vue-property-decorator';
import {CButton, CModal} from '@coreui/vue/src';
import ModalBase from '@/components/ModalBase.vue';

import {cilTrash} from '@coreui/icons';
import {extendedErrorToast} from '@/helpers/errorToast';
import DaemonMessageOptions from '@/ws/DaemonMessageOptions';

import SchedulerService from '@/services/SchedulerService';

import {AxiosError} from 'axios';
import {MutationPayload} from 'vuex';

@Component({
	components: {
		CButton,
		CModal,
	},
	data: () => ({
		cilTrash,
	}),
})
export default class TaskDeleteModal extends ModalBase {
	/**
	 * @property {number} syncTaskId Task ID
	 */
	@PropSync('taskId') syncTaskId!: number;

	/**
	 * @var {string} msgId Daemon API message ID
	 */
	private msgId = '';

	/**
	 * Component unsubscribe function
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
			this.$store.dispatch('spinner/hide');
			this.$store.dispatch('daemonClient/removeMessage', mutation.payload.data.msgId);
			if (mutation.payload.mType === 'mngScheduler_RemoveTask') {
				this.handleRemoveTask(mutation.payload.data);
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
	 * Removes a scheduler task
	 */
	private removeTask(): void {
		this.closeModal();
		if (this.$store.getters['daemonClient/isConnected']) {
			this.$store.dispatch('spinner/show', 30000);
			SchedulerService.removeTask(this.syncTaskId, new DaemonMessageOptions(null, 30000, 'config.daemon.scheduler.messages.deleteFail', () => this.msgId = ''))
				.then((msgId: string) => this.msgId = msgId);
		} else {
			this.$store.commit('spinner/SHOW');
			SchedulerService.removeTaskREST(this.syncTaskId)
				.then(() => {
					this.$store.commit('spinner/HIDE');
					this.$toast.success(
						this.$t('config.daemon.scheduler.messages.deleteSuccess').toString()
					);
					this.$emit('deleted');
				})
				.catch((error: AxiosError) => extendedErrorToast(error, 'config.daemon.scheduler.messages.deleteFailedRest', {task: this.syncTaskId}));
		}
	}

	/**
	 * Handles Daemon API RemoveTask response
	 * @param response Daemon API response
	 */
	private handleRemoveTask(response): void {
		if (response.status === 0) {
			this.$toast.success(
				this.$t('config.daemon.scheduler.messages.deleteSuccess').toString()
			);
			this.$emit('deleted');
		} else {
			this.$toast.error(
				this.$t('config.daemon.scheduler.messages.deleteFail').toString()
			);
		}
	}
}
</script>
