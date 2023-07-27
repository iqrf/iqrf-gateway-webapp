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
		v-model='showModal'
		width='50%'
		persistent
		no-click-animation
	>
		<v-card v-if='taskId !== null'>
			<v-card-title>
				<h5>{{ $t('config.daemon.scheduler.modal.title') }}</h5>
			</v-card-title>
			<v-card-text>
				{{ $t('config.daemon.scheduler.modal.deletePrompt', {task: taskId}) }}
			</v-card-text>
			<v-card-actions>
				<v-spacer />
				<v-btn
					@click='hideModal'
				>
					{{ $t('forms.cancel') }}
				</v-btn>
				<v-btn
					color='error'
					@click='removeTask'
				>
					{{ $t('table.actions.delete') }}
				</v-btn>
			</v-card-actions>
		</v-card>
	</v-dialog>
</template>

<script lang='ts'>
import {Component, VModel, Vue} from 'vue-property-decorator';

import {extendedErrorToast} from '@/helpers/errorToast';
import DaemonMessageOptions from '@/ws/DaemonMessageOptions';

import SchedulerService from '@/services/SchedulerService';

import {AxiosError} from 'axios';
import {MutationPayload} from 'vuex';

@Component
export default class TaskDeleteModal extends Vue {

	/**
	 * @property {string|null} taskId Task to delete
	 */
	@VModel({required: true}) taskId!: string|null;

	/**
	 * @var {string} msgId Daemon API message ID
	 */
	private msgId = '';

	/**
	 * Component unsubscribe function
	 */
	private unsubscribe: CallableFunction = () => {return;};

	/**
	 * Computes modal display condition
	 */
	get showModal(): boolean {
		return this.taskId !== null;
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
		if (this.taskId === null) {
			return;
		}
		const id = this.taskId;
		if (this.$store.getters['daemonClient/isConnected']) {
			this.$store.dispatch('spinner/show', 30000);
			SchedulerService.removeTask(id, new DaemonMessageOptions(null, 30000, 'config.daemon.scheduler.messages.deleteFail', () => this.msgId = ''))
				.then((msgId: string) => this.msgId = msgId);
		} else {
			this.$store.commit('spinner/SHOW');
			SchedulerService.removeTaskREST(id)
				.then(() => {
					this.$store.commit('spinner/HIDE');
					this.$toast.success(
						this.$t('config.daemon.scheduler.messages.deleteSuccess').toString()
					);
					this.hideModal();
					this.$emit('deleted');
				})
				.catch((error: AxiosError) => extendedErrorToast(error, 'config.daemon.scheduler.messages.deleteFailedRest', {task: id}));
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
			this.hideModal();
			this.$emit('deleted');
		} else {
			this.$toast.error(
				this.$t('config.daemon.scheduler.messages.deleteFail').toString()
			);
		}
	}

	/**
	 * Hides modal window
	 */
	private hideModal(): void {
		this.taskId = null;
	}
}
</script>
