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
				{{ $t('config.daemon.messagings.deleteDialog.title', {messaging: messagingType}) }}
			</h5>
		</template>
		{{ $t('config.daemon.messagings.deleteDialog.prompt', {messaging: messagingType, instance: instance}) }}
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
import {MessagingTypes} from '@/enums/Config/Messagings';

import DaemonConfigurationService from '@/services/DaemonConfigurationService';

import {AxiosError} from 'axios';


/**
 * Messaging delete dialog component
 */
@Component({
	components: {
		CButton,
		CModal,
	},
})
export default class MessagignDeleteModal extends ModalBase {
	/**
	 * @var {MessagingTypes} messagingType Messaging type
	 */
	private messagingType: MessagingTypes|null = null;

	/**
	 * @var {string} instance Messaging instance name
	 */
	private instance = '';

	/**
	 * @constant {Record<MessagingTypes, string>} componentNames Component names
	 */
	private componentNames: Record<MessagingTypes, string> = {
		MQ: 'iqrf::MqMessaging',
		MQTT: 'iqrf::MqttMessaging',
		UDP: 'iqrf::UdpMessaging',
	};

	/**
	 * Removes instance of UDP messaging component
	 */
	private remove(): void {
		if (this.messagingType === null || this.instance.length === 0) {
			return;
		}
		const type = this.messagingType;
		this.$store.commit('spinner/SHOW');
		DaemonConfigurationService.deleteInstance(this.componentNames[type], this.instance)
			.then(() => {
				this.$store.commit('spinner/HIDE');
				this.$toast.success(
					this.$t('config.daemon.messagings.deleteDialog.success', {messaging: type, instance: this.instance})
						.toString()
				);
				this.hideModal();
				this.$emit('deleted');
			})
			.catch((error: AxiosError) => extendedErrorToast(error, 'config.daemon.messagings.deleteDialog.failed', {messaging: type, instance: this.instance}));
	}

	/**
	 * Stores messaging type and instance, and shows modal window
	 * @param {MessagingTypes} type Messaging type
	 * @param {string} instance Component instance
	 */
	public showModal(type: MessagingTypes, instance: string): void {
		this.messagingType = type;
		this.instance = instance;
		this.openModal();
	}

	/**
	 * Resets messaging type and instance, and hides modal window
	 */
	private hideModal(): void {
		this.messagingType = null;
		this.instance = '';
		this.closeModal();
	}
}
</script>
