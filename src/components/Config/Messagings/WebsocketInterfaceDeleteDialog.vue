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
	<v-dialog
		v-model='show'
		width='50%'
		persistent
		no-click-animation
	>
		<template #activator='{attrs, on}'>
			<v-btn
				color='error'
				small
				v-bind='attrs'
				v-on='on'
				@click='openDialog'
			>
				<v-icon small>
					mdi-delete
				</v-icon>
				{{ $t('table.actions.delete') }}
			</v-btn>
		</template>
		<v-card>
			<v-card-title>{{ $t('config.daemon.messagings.websocket.removeDialog.interfaceTitle') }}</v-card-title>
			<v-card-text>{{ $t('config.daemon.messagings.websocket.removeDialog.interfacePrompt', {instance: instance.instanceMessaging}) }}</v-card-text>
			<v-card-actions>
				<v-spacer />
				<v-btn
					@click='closeDialog'
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
import {Component, Prop} from 'vue-property-decorator';
import DialogBase from '@/components/DialogBase.vue';

import {extendedErrorToast} from '@/helpers/errorToast';

import DaemonConfigurationService from '@/services/DaemonConfigurationService';

import {AxiosError} from 'axios';
import {IWsInterface} from '@/interfaces/Config/Messaging';

/**
 * Websocket interface delete dialog component
 */
@Component
export default class WebsocketInterfaceDeleteDialog extends DialogBase {
	/**
	 * @property {IWsInterface} instance WebSocket interface instance
	 */
	@Prop({required: true}) instance!: IWsInterface;

	/**
	 * Removes an existing instance of Websocket interface component
	 */
	private remove(): void {
		this.closeDialog();
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
