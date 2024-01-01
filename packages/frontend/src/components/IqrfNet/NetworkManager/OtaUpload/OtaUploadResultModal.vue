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
		v-model='show'
		width='50%'
		persistent
		no-click-animation
	>
		<v-card>
			<v-card-title>
				{{ modalTitle }}
			</v-card-title>
			<v-card-text>
				<v-data-table
					:items='results'
					:headers='headers'
				>
					<template #[`item.result`]='{item}'>
						<v-icon
							:color='item.result ? "success" : "error"'
						>
							{{ item.result ? 'mdi-check-circle-outline' : 'mdi-close-circle-outline' }}
						</v-icon>
					</template>
				</v-data-table>
			</v-card-text>
			<v-card-actions>
				<v-spacer />
				<v-btn
					@click='closeModal'
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

import {OtaUploadAction} from '@/enums/IqrfNet/OtaUpload';

import {DataTableHeader} from 'vuetify';
import {IOtaUploadResult} from '@/interfaces/DaemonApi/Iqmesh/OtaUpload';

/**
 * OTA upload result modal component
 */
@Component
export default class OtaUploadResultModal extends ModalBase {
	/**
	 * @property {Array<IOtaUploadResult>} results OTA upload results
	 */
	@Prop({required: true, type: Array, default: []}) results!: Array<IOtaUploadResult>;

	/**
	 * @var {OtaUploadAction} action OTA upload action
	 */
	private action: OtaUploadAction = OtaUploadAction.VERIFY;

	/**
	 * Computes modal window title depending on OTA upload action
	 */
	get modalTitle(): string {
		if (this.action === OtaUploadAction.VERIFY) {
			return this.$t('iqrfnet.networkManager.otaUpload.resultModal.verifyTitle').toString();
		}
		return this.$t('iqrfnet.networkManager.otaUpload.resultModal.loadTitle').toString();
	}

	/**
	 * @constant {Array<DataTableHeader>} headers Data table headers
	 */
	private readonly headers: Array<DataTableHeader> = [
		{
			text: this.$t('iqrfnet.networkManager.otaUpload.resultModal.address').toString(),
			value: 'address',
		},
		{
			text: this.$t('iqrfnet.networkManager.otaUpload.resultModal.success').toString(),
			value: 'result',
			filterable: false,
			sortable: false,
		},
	];

	/**
	 * Show modal window
	 * @param {OtaUploadAction} action OTA upload action
	 */
	public showModal(action: OtaUploadAction): void {
		this.action = action;
		this.show = true;
	}
}
</script>
