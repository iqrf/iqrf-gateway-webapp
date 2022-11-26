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
	<CModal
		v-show='show'
		:show.sync='show'
		color='danger'
		size='lg'
		:close-on-backdrop='false'
		:fade='false'
	>
		<template #header>
			<h5 class='modal-title'>
				{{ $t('config.daemon.misc.monitor.modal.title') }}
			</h5>
		</template>
		{{ $t('config.daemon.misc.monitor.modal.prompt', {instance: monitorInstance}) }}
		<template #footer>
			<CButton
				class='mr-1'
				color='secondary'
				@click='hideModal'
			>
				{{ $t('forms.cancel') }}
			</CButton>
			<CButton
				color='danger'
				@click='remove'
			>
				{{ $t('forms.delete') }}
			</CButton>
		</template>
	</CModal>
</template>

<script lang='ts'>
import {Component} from 'vue-property-decorator';
import {CButton, CModal} from '@coreui/vue/src';
import ModalBase from '@/components/ModalBase.vue';

import {extendedErrorToast} from '@/helpers/errorToast';

import DaemonConfigurationService from '@/services/DaemonConfigurationService';

import {AxiosError} from 'axios';

/**
 * Monitor delete modal component
 */
@Component({
	components: {
		CButton,
		CModal,
	},
})
export default class MonitorDeleteModal extends ModalBase {
	/**
	 * @constant {string} monitorComponent Monitor component
	 */
	private readonly monitorComponent = 'iqrf::MonitorService';

	/**
	 * @constant {string} websocketComponent Websocket component
	 */
	private readonly websocketComponent = 'shape::WebsocketCppService';

	/**
	 * @var {string} monitorInstance Monitor instance
	 */
	private monitorInstance = '';

	/**
	 * @var {string} websocketInstance Websocket instance
	 */
	private websocketInstance = '';

	/**
	 * Removes instance of the monitoring component
	 */
	private remove(): void {
		Promise.all([
			DaemonConfigurationService.deleteInstance(this.monitorComponent, this.monitorInstance),
			DaemonConfigurationService.deleteInstance(this.websocketComponent, this.websocketInstance),
		])
			.then(() => {
				this.$store.commit('spinner/HIDE');
				this.$toast.success(
					this.$t(
						'config.daemon.misc.monitor.messages.deleteSuccess',
						{instance: this.monitorInstance}
					).toString()
				);
				this.hideModal();
				this.$emit('deleted');
			})
			.catch((error: AxiosError) => {
				extendedErrorToast(
					error,
					'config.daemon.misc.monitor.messages.deleteFailed',
					{instance: this.monitorInstance},
				);
			});
	}

	/**
	 * Stores monitor and websocket instance, and shows modal window
	 * @param {string} monitor Monitor instance
	 * @param {string} websocket Websocket instance
	 */
	public showModal(monitor: string, websocket: string): void {
		this.monitorInstance = monitor;
		this.websocketInstance = websocket;
		this.openModal();
	}

	/**
	 * Resets websocket type and instance, and hides modal window
	 */
	private hideModal(): void {
		this.monitorInstance = '';
		this.websocketInstance = '';
		this.closeModal();
	}
}
</script>
