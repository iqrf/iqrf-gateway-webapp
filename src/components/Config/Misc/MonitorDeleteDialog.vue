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
				@click='openDialog'
				v-on='on'
			>
				<v-icon small>
					mdi-delete
				</v-icon>
				{{ $t('table.actions.delete') }}
			</v-btn>
		</template>
		<v-card>
			<v-card-title>{{ $t('config.daemon.misc.monitor.modal.title') }}</v-card-title>
			<v-card-text>{{ $t('config.daemon.misc.monitor.modal.prompt', {instance: instance.monitor.instance}) }}</v-card-text>
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
import {IMonitorWsInstance} from '@/interfaces/Config/Misc';


/**
 * Monitor delete dialog
 */
@Component
export default class MonitorDeleteDialog extends DialogBase {
	/**
	 * @property {IMonitorWsInstance} instance Monitor websocket instance
	 */
	@Prop({required: true}) instance!: IMonitorWsInstance;

	/**
	 * Removes instance of the monitoring component
	 */
	private remove(): void {
		this.closeDialog();
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
