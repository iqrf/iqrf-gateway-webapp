<template>
	<v-dialog
		v-model='show'
		width='50%'
		persistent
		no-click-animation
	>
		<v-card>
			<v-card-title>
				{{ $t('iqrfnet.networkManager.bondingManager.modal.unbondTitle') }}
			</v-card-title>
			<v-card-text>
				{{ $t('iqrfnet.networkManager.bondingManager.modal.unbondPrompt', {address: address}) }}
			</v-card-text>
			<v-card-actions>
				<v-spacer />
				<v-btn
					@click='closeDialog'
				>
					{{ $t('forms.cancel') }}
				</v-btn>
				<v-btn
					color='error'
					@click='unbond'
				>
					{{ $t('forms.unbond') }}
				</v-btn>
			</v-card-actions>
		</v-card>
	</v-dialog>
</template>

<script lang='ts'>
import {Component, Prop} from 'vue-property-decorator';
import DialogBase from '@/components/DialogBase.vue';

/**
 * IQMESH unbond node confirmation dialog component
 */
@Component
export default class UnbondConfirmationDialog extends DialogBase {
	/**
	 * @property {number} address Device address
	 */
	@Prop({type: Number, default: 1}) address!: number;

	/**
	 * Shows dialog from the outside
	 */
	public showDialog(): void {
		this.openDialog();
	}

	/**
	 * Emits event to unbond node
	 */
	private unbond(): void {
		this.closeDialog();
		this.$emit('unbond');
	}
}
</script>
