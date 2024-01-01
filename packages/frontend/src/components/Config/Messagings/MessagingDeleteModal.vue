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
		v-model='showModal'
		width='50%'
		persistent
		no-click-animation
	>
		<v-card v-if='instance !== null'>
			<v-card-title>
				{{ $t('config.daemon.messagings.deleteDialog.title', {messaging: messagingType}) }}
			</v-card-title>
			<v-card-text>
				{{ $t('config.daemon.messagings.deleteDialog.prompt', {messaging: messagingType, instance: instance}) }}
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
					@click='remove'
				>
					{{ $t('forms.delete') }}
				</v-btn>
			</v-card-actions>
		</v-card>
	</v-dialog>
</template>

<script lang='ts'>
import {Component, VModel, Prop, Vue} from 'vue-property-decorator';

import {extendedErrorToast} from '@/helpers/errorToast';
import {MessagingTypes} from '@/enums/Config/Messagings';

import DaemonConfigurationService from '@/services/DaemonConfigurationService';

import {AxiosError} from 'axios';

/**
 * Messaging delete dialog component
 */
@Component
export default class MessagignDeleteModal extends Vue {

	/**
	 * @property {string|null} instance Messaging instance to delete
	 */
	@VModel({required: true}) instance!: string|null;

	/**
	 * @property {MessagingType} messagingType Messaging type
	 */
	@Prop({required: true}) messagingType!: MessagingTypes;

	/**
	 * @constant {Record<MessagingTypes, string>} componentNames Component names
	 */
	private readonly componentNames: Record<MessagingTypes, string> = {
		MQ: 'iqrf::MqMessaging',
		MQTT: 'iqrf::MqttMessaging',
		UDP: 'iqrf::UdpMessaging',
	};

	/**
	 * Computes modal display condition
	 */
	get showModal(): boolean {
		return this.instance !== null;
	}

	/**
	 * Removes instance of UDP messaging component
	 */
	private remove(): void {
		if (this.instance === null) {
			return;
		}
		const instance = this.instance;
		const type = this.messagingType;
		this.$store.commit('spinner/SHOW');
		DaemonConfigurationService.deleteInstance(this.componentNames[type], instance)
			.then(() => {
				this.$store.commit('spinner/HIDE');
				this.$toast.success(
					this.$t('config.daemon.messagings.deleteDialog.success', {messaging: type, instance: instance})
						.toString()
				);
				this.hideModal();
				this.$emit('deleted');
			})
			.catch((error: AxiosError) => extendedErrorToast(error, 'config.daemon.messagings.deleteDialog.failed', {messaging: type, instance: instance}));
	}

	/**
	 * Hides modal window
	 */
	private hideModal(): void {
		this.instance = null;
	}
}
</script>
