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
		width='auto'
		persistent
		no-click-animation
	>
		<v-card>
			<v-card-title>
				{{ $t('iqrfnet.networkManager.otaUpload.resultDialog.verifyTitle') }}
			</v-card-title>
			<v-card-text>
				<v-data-table
					:items='syncResult'
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
					@click='closeDialog'
				>
					{{ $t('forms.close') }}
				</v-btn>
			</v-card-actions>
		</v-card>
	</v-dialog>
</template>

<script lang='ts'>
import {Component, PropSync} from 'vue-property-decorator';
import DialogBase from '@/components/DialogBase.vue';

import {DataTableHeader} from 'vuetify';
import {IOtaUploadResult} from '@/interfaces/IqrfNet/NetworkManager';

@Component({})

/**
 * OTA upload results dialog component
 */
export default class OtaUploadVerifyResultDialog extends DialogBase {
	/**
	 * @property {Array<IOtaUploadResult>} syncResult OTA upload results
	 */
	@PropSync('result') syncResult!: Array<IOtaUploadResult>;

	/**
	 * @constant {Array<DataTableHeader>} headers Data table headers
	 */
	private headers: Array<DataTableHeader> = [
		{
			text: this.$t('iqrfnet.networkManager.otaUpload.resultDialog.address').toString(),
			value: 'address',
		},
		{
			text: this.$t('iqrfnet.networkManager.otaUpload.resultDialog.success').toString(),
			value: 'result',
		},
	];

	/**
	 * Opens dialog
	 */
	public showDialog(): void {
		this.openDialog();
	}
}
</script>
