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
					{{ $t('config.daemon.messagings.websocket.removeDialog.interfaceTitle') }}
				</h5>
			</template>
			{{ $t('config.daemon.messagings.websocket.removeDialog.interfacePrompt', {instance: instance.instanceMessaging}) }}
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
import {IWsInterface} from '@/interfaces/Config/Messaging';

/**
 * Websocket interface delete dialog component
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
export default class WebsocketInterfaceDeleteModal extends ModalBase {
	/**
	 * @property {IWsInterface} instance WebSocket interface instance
	 */
	@Prop({required: true}) instance!: IWsInterface;

	/**
	 * Removes an existing instance of Websocket interface component
	 */
	private remove(): void {
		this.closeModal();
		this.$store.commit('spinner/SHOW');
		Promise.all([
			DaemonConfigurationService.deleteInstance(this.instance.messaging.component, this.instance.messaging.instance),
			DaemonConfigurationService.deleteInstance(this.instance.service.component, this.instance.service.instance),
		])
			.then(() => {
				this.$store.commit('spinner/HIDE');
				this.$toast.success(
					this.$t(
						'config.daemon.messagings.websocket.removeDialog.interfaceDeleteSuccess',
						{instance: this.instance.messaging.instance},
					).toString()
				);
				this.$emit('deleted');
			})
			.catch((error: AxiosError) => {
				extendedErrorToast(
					error,
					'config.daemon.messagings.websocket.removeDialog.interfaceDeleteFailed',
					{interface: this.instance.messaging.instance},
				);
			});
	}
}

</script>