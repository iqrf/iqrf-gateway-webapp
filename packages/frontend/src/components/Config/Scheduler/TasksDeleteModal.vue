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
	<v-dialog
		v-model='show'
		width='50%'
		persistent
		no-click-animation
	>
		<template #activator='{on, attrs}'>
			<v-btn
				color='error'
				small
				v-bind='attrs'
				v-on='on'
				@click='openModal'
			>
				<v-icon small>
					mdi-delete
				</v-icon>
				{{ $t('table.actions.deleteAll') }}
			</v-btn>
		</template>
		<v-card>
			<v-card-title>
				{{ $t('config.daemon.scheduler.modal.title') }}
			</v-card-title>
			<v-card-text>
				{{ $t('config.daemon.scheduler.modal.deleteAllPrompt') }}
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
					@click='removeAllTasks'
				>
					{{ $t('table.actions.deleteAll') }}
				</v-btn>
			</v-card-actions>
		</v-card>
	</v-dialog>
</template>

<script lang='ts'>
import {Component} from 'vue-property-decorator';

import DaemonMessageOptions from '@/ws/DaemonMessageOptions';
import {extendedErrorToast} from '@/helpers/errorToast';

import SchedulerService from '@/services/SchedulerService';

import {AxiosError} from 'axios';
import {MutationPayload} from 'vuex';
import ModalBase from '@/components/ModalBase.vue';

/**
 * Scheduler tasks delete all modal component
 */
@Component
export default class TasksDeleteModal extends ModalBase {

	/**
	 * @var {string} msgId Daemon API msg ID
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
			if (mutation.payload.mType === 'mngScheduler_RemoveAll') {
				this.handleRemoveAll(mutation.payload.data);
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
	 * Removes all scheduler tasks
	 */
	private removeAllTasks(): void {
		this.closeModal();
		if (this.$store.getters['daemonClient/isConnected']) {
			this.$store.dispatch('spinner/show', 30000);
			SchedulerService.removeAll(new DaemonMessageOptions(null, 30000, 'config.daemon.scheduler.messages.deleteAllFailed', () => this.msgId = ''))
				.then((msgId: string) => this.msgId = msgId);
		} else {
			this.$store.commit('spinner/SHOW');
			SchedulerService.removeAllRest()
				.then(() => {
					this.$store.commit('spinner/HIDE');
					this.$toast.success(
						this.$t('config.daemon.scheduler.messages.deleteAllSuccess').toString()
					);
					this.$emit('deleted');
				})
				.catch((error: AxiosError) => extendedErrorToast(error, 'config.daemon.scheduler.messages.deleteAllFailedRest'));
		}
	}

	/**
	 * Handles Daemon API RemoveAll response
	 * @param response Daemon API response
	 */
	private handleRemoveAll(response): void {
		if (response.status === 0) {
			this.$toast.success(
				this.$t('config.daemon.scheduler.messages.deleteAllSuccess').toString()
			);
			this.$emit('deleted');
		} else {
			this.$toast.error(
				this.$t('config.daemon.scheduler.messages.deleteAllFailed').toString()
			);
		}
	}
}
</script>
