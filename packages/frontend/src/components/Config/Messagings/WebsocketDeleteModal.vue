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
		v-model='showModal'
		width='50%'
		persistent
		no-click-animation
	>
		<v-card v-if='instance !== null'>
			<v-card-title>
				{{ $t('config.daemon.messagings.websocket.removeDialog.title', {type: componentType}) }}
			</v-card-title>
			<v-card-text>
				{{ $t('config.daemon.messagings.websocket.removeDialog.prompt', {type: componentType, instance: instance.instance}) }}
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
import {Component, Prop, VModel, Vue} from 'vue-property-decorator';

import {extendedErrorToast} from '@/helpers/errorToast';
import {WebsocketTypes} from '@/enums/Config/Messagings';

import DaemonConfigurationService from '@/services/DaemonConfigurationService';

import {AxiosError} from 'axios';
import {IWsMessaging, IWsService} from '@/interfaces/Config/Messaging';

/**
 * Websocket delete dialog component
 */
@Component
export default class WebsocketDeleteDialog extends Vue {
	/**
	 * @property {IWsService|IWsMessaging} instance Instance to delete
	 */
	@VModel({required: true}) instance!: IWsService|IWsMessaging|null;

	/**
	 * @property {WebsocketTypes} componentType WebSocket component type
	 */
	@Prop({required: true}) componentType!: WebsocketTypes;

	/**
	 * Computes modal display condition
	 */
	get showModal(): boolean {
		return this.instance !== null;
	}

	/**
	 * Removes an existing instance of Websocket component
	 */
	private remove(): void {
		if (this.instance === null) {
			return;
		}
		const instance = this.instance.instance;
		this.$store.commit('spinner/SHOW');
		DaemonConfigurationService.deleteInstance(this.instance.component, instance)
			.then(() => {
				this.$store.commit('spinner/HIDE');
				this.$toast.success(
					this.$t('config.daemon.messagings.websocket.removeDialog.deleteSuccess', {type: this.componentType, instance: instance})
						.toString()
				);
				this.hideModal();
				this.$emit('deleted');
			})
			.catch((error: AxiosError) => {
				extendedErrorToast(
					error,
					'config.daemon.messagings.websocket.removeDialog.deleteFailed',
					{type: this.componentType, instance: instance}
				);
			});
	}

	/**
	 * Hides modal window
	 */
	private hideModal(): void {
		this.instance = null;
	}
}
</script>
