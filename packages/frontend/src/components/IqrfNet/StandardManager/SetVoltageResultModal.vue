<template>
	<v-dialog
		v-model='show'
		width='50%'
		persistent
		no-click-animation
	>
		<v-card>
			<v-card-title>{{ $t('iqrfnet.standard.light.result.voltage.title') }}</v-card-title>
			<v-card-text>
				<v-data-table
					v-if='result !== null'
					:headers='headers'
					:items='[result]'
					dense
					hide-default-footer
				>
					<template #[`item.previousVoltage`]='{item}'>
						<td>{{ item.previousVoltage }} {{ $t('iqrfnet.standard.light.result.voltage.unit') }}</td>
					</template>
					<template #[`item.currentVoltage`]='{item}'>
						<td>{{ item.currentVoltage }} {{ $t('iqrfnet.standard.light.result.voltage.unit') }}</td>
					</template>
				</v-data-table>
			</v-card-text>
			<v-card-actions>
				<v-spacer />
				<v-btn
					@click='close'
				>
					{{ $t('forms.close') }}
				</v-btn>
			</v-card-actions>
		</v-card>
	</v-dialog>
</template>

<script lang='ts'>
import {Component, Prop} from 'vue-property-decorator';
import ModalBase from '@/components/ModalBase.vue';

import { type SetLaiResult } from '@/interfaces/DaemonApi/Standard';
import { DataTableHeader } from 'vuetify';

/**
 * Standard Light LDI commands result modal
 */
@Component
export default class SetVoltageResultModal extends ModalBase {

	/**
	 * @property {number|null} result Set LAI voltage result
	 */
	@Prop({ default: null }) readonly result!: SetLaiResult|null;

	private readonly headers: Array<DataTableHeader> = [
		{
			text: this.$t('forms.fields.deviceAddr').toString(),
			value: 'address',
			sortable: false,
		},
		{
			text: this.$t('iqrfnet.standard.light.result.voltage.old').toString(),
			value: 'previousVoltage',
			sortable: false,
		},
		{
			text: this.$t('iqrfnet.standard.light.result.voltage.new').toString(),
			value: 'currentVoltage',
			sortable: false,
		}
	];

	/**
	 * Open modal window
	 */
	public open(): void {
		this.openModal();
	}

	/**
	 * Close modal window
	 */
	public close(): void {
		this.closeModal();
	}
}
</script>
