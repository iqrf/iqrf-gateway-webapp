<template>
	<v-dialog
		v-model='show'
		width='50%'
		persistent
		no-click-animation
	>
		<v-card>
			<v-card-title>{{ $t('iqrfnet.standard.light.result.ldiCommand.title') }}</v-card-title>
			<v-card-text>
				<v-data-table
					:headers='headers'
					:items='result'
					dense
					hide-default-footer
					:page.sync='page'
					@page-count='pageCount = $event'
				>
					<template #[`item.status`]='{item}'>
						<td>
							<span :class='getStatusClass(item.status)'>{{ getStatusString(item.status) }}</span>
						</td>
					</template>
					<template #footer>
						<v-pagination
							v-model='page'
							:length='pageCount'
						/>
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

import {DataTableHeader} from 'vuetify';
import { type LightAnswerResult } from '@/interfaces/DaemonApi/Standard';

/**
 * Standard Light LDI commands result modal
 */
 @Component
export default class LdiCommandsResultModal extends ModalBase {

	/**
	 * @property {LightAnswerResult[]} results LDI commands result
	 */
	@Prop({ default: [], required: false }) readonly result!: LightAnswerResult[];

	private page = 1;

	private pageCount = 0;

	/**
	 * @constant {Array<DataTableHeader>} headers Data table headers
	 */
	private readonly headers: Array<DataTableHeader> = [
		{
			text: this.$t('forms.fields.deviceAddr').toString(),
			value: 'address',
		},
		{
			text: this.$t('iqrfnet.standard.light.form.ldiCommand.title').toString(),
			value: 'command',
		},
		{
			text: this.$t('iqrfnet.standard.light.result.ldiCommand.status').toString(),
			value: 'status',
			filterable: false,
		},
		{
			text: this.$t('iqrfnet.standard.light.result.ldiCommand.value').toString(),
			value: 'value',
			filterable: false,
		},
	];

	private getStatusString(status: number): string {
		let message = '';
		if (status === 128) {
			message = 'iqrfnet.standard.light.result.ldiCommand.statuses.notReceived';
		} else if (status === 129) {
			message = 'iqrfnet.standard.light.result.ldiCommand.statuses.received';
		} else {
			message = 'iqrfnet.standard.light.result.ldiCommand.statuses.error';
		}
		return this.$t(message).toString();
	}

	private getStatusClass(status: number): string|null {
		if (status === 128) {
			return null;
		}
		if (status === 129) {
			return 'text-success';
		}
		return 'text-danger';
	}

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
