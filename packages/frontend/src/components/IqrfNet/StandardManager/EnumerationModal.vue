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
	<v-dialog
		v-model='show'
		width='50%'
		persistent
		no-click-animation
	>
		<template #activator='{ on }'>
			<v-btn
				class='mr-1'
				color='primary'
				small
				v-on='on'
				@click='start'
			>
				<v-icon small>
					mdi-google-spreadsheet
				</v-icon>
				<span class='d-none d-lg-inline'>
					{{ $t('iqrfnet.standard.table.actions.enumerate') }}
				</span>
			</v-btn>
		</template>
		<v-card>
			<v-card-title>
				{{ $t('iqrfnet.standard.enumeration.title') }}
			</v-card-title>
			<v-card-text>
				<v-simple-table>
					<tr>
						<th>{{ $t('iqrfnet.standard.enumeration.step') }}</th>
						<td>{{ status.step }} / 8</td>
					</tr>
					<tr>
						<th>{{ $t('iqrfnet.standard.enumeration.time') }}</th>
						<td>{{ timeString }}</td>
					</tr>
				</v-simple-table>
				<v-divider class='my-2' />
				<div class='text-center'>
					<v-progress-linear
						class='mb-2'
						:color='status.color'
						:value='(status.step / 8) * 100'
						rounded
						height='15'
					/>
					<div>{{ status.message }}</div>
				</div>
				<v-divider class='mt-2' />
			</v-card-text>
			<v-card-actions>
				<v-spacer />
				<v-btn
					color='primary'
					:disabled='!status.finished'
					@click='close'
				>
					{{ $t('forms.close') }}
				</v-btn>
			</v-card-actions>
		</v-card>
	</v-dialog>
</template>

<script lang='ts'>
import {Component} from 'vue-property-decorator';
import ModalBase from '@/components/ModalBase.vue';

import DaemonMessageOptions from '@/ws/DaemonMessageOptions';
import DbService from '@/services/DaemonApi/DbService';
import Timer from '@/helpers/timer';

import { MutationPayload } from 'vuex';

@Component
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

	/**
	 * Registers mutation handler
	 */
	created(): void {
		this.unsubscribe = this.$store.subscribe((mutation: MutationPayload) => {
			if (mutation.type === 'daemonClient/SOCKET_ONCLOSE') {
				this.$store.dispatch('daemonClient/removeMessage', this.msgId);
				this.status.color = 'error';
				this.status.message = this.$t('iqrfnet.standard.enumeration.messages.conn').toString();
				this.finish();
			}
			if (mutation.type !== 'daemonClient/SOCKET_ONMESSAGE') {
				return;
			}
			if (mutation.payload.data.msgId !== this.msgId) {
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
	private start(): void {
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

<style scoped>
table {
	width: 100%;
}

th {
	text-align: left;
}

td {
	text-align: right;
}
</style>

