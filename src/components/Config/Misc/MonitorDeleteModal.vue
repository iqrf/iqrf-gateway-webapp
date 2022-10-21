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
			<CIcon
				:content='cilTrash'
				size='sm'
			/>
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
					{{ $t('config.daemon.misc.monitor.modal.title') }}
				</h5>
			</template>
			{{ $t('config.daemon.misc.monitor.modal.prompt', {instance: instance.monitor.instance}) }}
			<template #footer>
				<CButton
					class='mr-1'
					color='secondary'
					@click='closeModal'
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
	</span>
</template>

<script lang='ts'>
import {Component, Prop} from 'vue-property-decorator';
import {CButton, CModal} from '@coreui/vue/src';
import ModalBase from '@/components/ModalBase.vue';

import {cilTrash} from '@coreui/icons';
import {extendedErrorToast} from '@/helpers/errorToast';

import DaemonConfigurationService from '@/services/DaemonConfigurationService';

import {AxiosError} from 'axios';
import {IMonitorComponent} from '@/interfaces/Config/Misc';

/**
 * Monitor delete modal component
 */
@Component({
	components: {
		CButton,
		CModal,
	},
	data: () => ({
		cilTrash,
	}),
})
export default class MonitorDeleteModal extends ModalBase {
	/**
	 * @property {IMonitorComponent} instance Monitor websocket instance
	 */
	@Prop({required: true}) instance!: IMonitorComponent;

	/**
	 * Removes instance of the monitoring component
	 */
	private remove(): void {
		this.closeModal();
		this.$store.commit('spinner/SHOW');
		Promise.all([
			DaemonConfigurationService.deleteInstance(this.instance.monitor.component, this.instance.monitor.instance),
			DaemonConfigurationService.deleteInstance(this.instance.webSocket.component, this.instance.webSocket.instance),
		])
			.then(() => {
				this.$store.commit('spinner/HIDE');
				this.$toast.success(
					this.$t(
						'config.daemon.misc.monitor.messages.deleteSuccess',
						{instance: this.instance.monitor.instance}
					).toString()
				);
				this.$emit('deleted');
			})
			.catch((error: AxiosError) => {
				extendedErrorToast(
					error,
					'config.daemon.misc.monitor.messages.deleteFailed',
					{instance: this.instance.monitor.instance},
				);
			});
	}
}
</script>
