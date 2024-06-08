<!--
Copyright 2017-2023 IQRF Tech s.r.o.
Copyright 2019-2023 MICRORISC s.r.o.

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
				{{ $t('iqrfnet.standard.light.result.ldiCommand.title') }}
			</h5>
		</template>
		<CDataTable
			:fields='fields'
			:items='result'
			:column-filter='true'
			:items-per-page='10'
			:pagination='true'
			:striped='true'
			:sorter='{external: false, resetable: true}'
		>
			<template #status='{item}'>
				<td>
					<span :class='getStatusClass(item.status)'>{{ getStatusString(item.status) }}</span>
				</td>
			</template>
		</CDataTable>
		<template #footer>
			<CButton
				color='secondary'
				@click='close'
			>
				{{ $t('forms.close') }}
			</CButton>
		</template>
	</CModal>
</template>

<script lang='ts'>
import {Component, Prop} from 'vue-property-decorator';
import {CButton, CDataTable, CIcon, CModal} from '@coreui/vue/src';
import ModalBase from '@/components/ModalBase.vue';

import {cilCheckCircle, cilXCircle} from '@coreui/icons';

import { type IField } from '@/interfaces/Coreui';
import { type LightAnswerResult } from '@/interfaces/DaemonApi/Standard';

@Component({
	components: {
		CButton,
		CDataTable,
		CIcon,
		CModal,
	},
	data: () => ({
		cilCheckCircle,
		cilXCircle,
	})
})

/**
 * Standard Light LDI commands result modal
 */
export default class LdiCommandsResultModal extends ModalBase {

	/**
	 * @property {LightAnswerResult[]} results LDI commands result
	 */
	@Prop({ default: [], required: false }) readonly result!: LightAnswerResult[];

	/**
	 * @constant {Array<IField>} fields Data table headers
	 */
	private fields: Array<IField> = [
		{
			label: this.$t('forms.fields.deviceAddr'),
			key: 'address',
		},
		{
			label: this.$t('iqrfnet.standard.light.form.ldiCommand.title'),
			key: 'command',
		},
		{
			label: this.$t('iqrfnet.standard.light.result.ldiCommand.status'),
			key: 'status',
			filter: false,
		},
		{
			label: this.$t('iqrfnet.standard.light.result.ldiCommand.value'),
			key: 'value',
			filter: false,
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
