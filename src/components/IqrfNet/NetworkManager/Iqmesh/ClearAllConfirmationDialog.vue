<template>
	<v-dialog
		v-model='show'
		width='50%'
		persistent
		no-click-animation
	>
		<v-card>
			<v-card-title>
				{{ $t('iqrfnet.networkManager.bondingManager.modal.clearAllTitle') }}
			</v-card-title>
			<v-card-text>
				{{ $t('iqrfnet.networkManager.bondingManager.modal.clearAllPrompt') }}
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
					@click='clear'
				>
					{{ $t('forms.clearBonds') }}
				</v-btn>
			</v-card-actions>
		</v-card>
	</v-dialog>
</template>

<script lang='ts'>
import {Component} from 'vue-property-decorator';
import DialogBase from '@/components/DialogBase.vue';

/**
 * IQMESH Clear all nodes confirmation dialog component
 */
@Component
export default class ClearAllConfirmationDialog extends DialogBase {
	/**
	 * Shows dialog from the outside
	 */
	public showDialog(): void {
		this.openDialog();
	}

	/**
	 * Emits event to clear all bonds
	 */
	private clear(): void {
		this.closeDialog();
		this.$emit('clear');
	}
}
</script>
