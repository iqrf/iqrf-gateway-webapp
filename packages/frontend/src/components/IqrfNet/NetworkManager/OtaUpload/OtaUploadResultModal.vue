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
	<CModal
		:show.sync='show'
		color='primary'
		size='lg'
		:close-on-backdrop='false'
		:fade='false'
	>
		<template #header>
			<h5 class='modal-title'>
				{{ modalTitle }}
			</h5>
		</template>
		<CDataTable
			:fields='headers'
			:items='results'
			:column-filter='true'
			:items-per-page='10'
			:striped='true'
			:sorter='{external: false, resetable: true}'
		>
			<template #result='{item}'>
				<td>
					<CIcon
						:class='item.result ? "text-success" : "text-danger"'
						:content='item.result ? cilCheckCircle : cilXCircle'
						size='xl'
					/>
				</td>
			</template>
		</CDataTable>
		<template #footer>
			<CButton
				color='secondary'
				@click='closeModal'
			>
				{{ $t('forms.close') }}
			</CButton>
		</template>
	</CModal>
</template>

<script lang='ts'>
import {Component, Prop} from 'vue-property-decorator';
import {CButton, CDataTable, CModal} from '@coreui/vue/src';
import ModalBase from '@/components/ModalBase.vue';

import {cilCheckCircle, cilXCircle} from '@coreui/icons';
import {OtaUploadAction} from '@/enums/IqrfNet/OtaUpload';

import {IField} from '@/interfaces/Coreui';
import {IOtaUploadResult} from '@/interfaces/DaemonApi/Iqmesh/OtaUpload';

/**
 * OTA upload result modal component
 */
@Component({
	components: {
		CButton,
		CDataTable,
		CModal,
	},
	data: () => ({
		cilCheckCircle,
		cilXCircle,
	}),
})
export default class OtaUploadResultModal extends ModalBase {
	/**
	 * @property {Array<IOtaUploadResult>} results OTA upload results
	 */
	@Prop({required: true, type: Array, default: []}) results!: Array<IOtaUploadResult>;

	/**
	 * @constant {Array<IField>} header Result data table headers
	 */
	private headers: Array<IField> = [
		{
			label: this.$t('iqrfnet.networkManager.otaUpload.resultModal.address').toString(),
			key: 'address',
			sorter: true,
			filter: true,
		},
		{
			label: this.$t('iqrfnet.networkManager.otaUpload.resultModal.success').toString(),
			key: 'result',
			sorter: true,
			filter: false,
		}
	];

	get modalTitle(): string {
		if (this.action === OtaUploadAction.VERIFY) {
			return this.$t('iqrfnet.networkManager.otaUpload.resultModal.verifyTitle').toString();
		}
		return this.$t('iqrfnet.networkManager.otaUpload.resultModal.loadTitle').toString();
	}

	private action: OtaUploadAction = OtaUploadAction.VERIFY;

	/**
	 * Show modal window
	 */
	public showModal(action: OtaUploadAction): void {
		this.action = action;
		this.show = true;
	}
}
</script>
