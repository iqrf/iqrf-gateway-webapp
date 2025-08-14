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
				{{ $t('iqrfnet.standard.light.result.voltage.title') }}
			</h5>
		</template>
		<table
			v-if='result !== null'
			style='width: 100%; table-layout: fixed;'
		>
			<tr style='border-bottom: solid 1px; border-bottom-color: #d8dbe0;'>
				<th>{{ $t('forms.fields.deviceAddr') }}</th>
				<th>{{ $t('iqrfnet.standard.light.result.voltage.old') }}</th>
				<th>{{ $t('iqrfnet.standard.light.result.voltage.new') }}</th>
			</tr>
			<tr>
				<td>{{ result.address }}</td>
				<td>{{ result.previousVoltage }} {{ $t('iqrfnet.standard.light.result.voltage.unit') }}</td>
				<td>{{ result.currentVoltage }} {{ $t('iqrfnet.standard.light.result.voltage.unit') }}</td>
			</tr>
		</table>
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
import { type SetLaiResult } from '@/interfaces/DaemonApi/Standard';

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
export default class SetVoltageResultModal extends ModalBase {

	/**
	 * @property {number|null} result Set LAI voltage result
	 */
	@Prop({ default: null }) readonly result!: SetLaiResult|null;

	/**
	 * @constant {Array<IField>} fields Data table headers
	 */
	private fields: Array<IField> = [
		{
			label: this.$t('forms.fields.deviceAddr'),
			key: 'address',
			filter: false,
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
