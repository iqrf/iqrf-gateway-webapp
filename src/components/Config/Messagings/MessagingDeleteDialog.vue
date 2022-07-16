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
				@click='openDialog'
			>
				<v-icon small>
					mdi-delete
				</v-icon>
				{{ $t('table.actions.delete') }}
			</v-btn>
		</template>
		<v-card>
			<v-card-title>
				{{ $t('config.daemon.messagings.deleteDialog.title', {messaging: messagingType}) }}
			</v-card-title>
			<v-card-text>
				{{ $t('config.daemon.messagings.deleteDialog.prompt', {messaging: messagingType, instance: instance}) }}
			</v-card-text>
			<v-card-actions>
				<v-spacer />
				<v-btn
					@click='closeDialog'
				>
					{{ $t('forms.cancel') }}
				</v-btn> <v-btn
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
import {Component, Prop, Vue} from 'vue-property-decorator';

import {extendedErrorToast} from '@/helpers/errorToast';
import {MessagingTypes} from '@/enums/Config/Messagings';

import DaemonConfigurationService from '@/services/DaemonConfigurationService';

import {AxiosError} from 'axios';

@Component({})

/**
 * Messaging delete dialog component
 */
export default class MessagingDeleteDialog extends Vue {
	/**
	 * @var {boolean} show Dialog visibility
	 */
	private show = false;

	/**
	 * @property {MessagingTypes} messagingType Messaging type
	 */
	@Prop({required: true}) messagingType!: MessagingTypes;

	/**
	 * @property {string} instance Messaging instance name
	 */
	@Prop({required: true}) instance!: string;


	private componentNames: Record<MessagingTypes, string> = {
		MQ: 'iqrf::MqMessaging',
		MQTT: 'iqrf::MqttMessaging',
		WS: 'WebSocketMessaging',
		UDP: 'iqrf::UdpMessaging',
	};

	/**
	 * Opens dialog
	 */
	private openDialog(): void {
		this.show = true;
	}

	/**
	 * Closes dialog
	 */
	private closeDialog(): void {
		this.show = false;
	}

	/**
	 * Removes instance of UDP messaging component
	 */
	private remove(): void {
		this.closeDialog();
		this.$store.commit('spinner/SHOW');
		DaemonConfigurationService.deleteInstance(this.componentNames[this.messagingType], this.instance)
			.then(() => {
				this.$store.commit('spinner/HIDE');
				this.$toast.success(
					this.$t('config.daemon.messagings.deleteDialog.success', {messaging: this.messagingType, instance: this.instance})
						.toString()
				);
				this.$emit('deleted');
			})
			.catch((error: AxiosError) => extendedErrorToast(error, 'config.daemon.messagings.deleteDialog.failed', {messaging: this.messagingType, instance: this.instance}));
	}
}
</script>
