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
	<span>
		<CButton
			color='primary'
			size='sm'
			:disabled='activatorDisabled'
			@click='openModal'
		>
			{{ $t('iqrfnet.networkManager.autoNetwork.form.bondingControl.mid.manual') }}
		</CButton>
		<CModal
			v-if='show'
			:show.sync='show'
			color='primary'
			size='lg'
			:close-on-backdrop='false'
			:fade='false'
		>
			<template #header>
				<h5 class='modal-title'>
					{{ $t('iqrfnet.networkManager.autoNetwork.midModal.title') }}
				</h5>
			</template>
			<CDataTable
				:fields='headers'
				:items='list'
				:pagination='true'
				:items-per-page='5'
				:column-filter='true'
				:striped='true'
				:sorter='{external: false, resetable: true}'
			>
				<template #no-items-view>
					{{ $t('iqrfnet.networkManager.autoNetwork.midModal.table.noItems') }}
				</template>
				<template #deviceMID='{item}'>
					<td class='col-2'>
						{{ item.deviceMID }}
					</td>
				</template>
				<template #deviceAddr='{item}'>
					<td class='col-4'>
						{{ item.deviceAddr ? item.deviceAddr : $t('iqrfnet.networkManager.autoNetwork.midModal.table.unspecified') }}
					</td>
				</template>
				<template #note='{item}'>
					<td>
						{{ item.note ? item.note.split(';').join(', ') : $t('iqrfnet.networkManager.autoNetwork.midModal.table.unspecified') }}
					</td>
				</template>
				<template #actions='{item}'>
					<td class='col-actions'>
						<CButton
							color='danger'
							size='sm'
							@click='removeFromList(list.indexOf(item))'
						>
							<CIcon :content='cilTrash' size='sm' />
						</CButton>
					</td>
				</template>
			</CDataTable><hr>
			<ValidationObserver v-slot='{invalid}' ref='form'>
				<CForm>
					<ValidationProvider
						v-slot='{errors, touched, valid}'
						:rules='{
							duplicateMid: true,
							regex: /^[0-9a-fA-F]{8}$/,
							required: true,
						}'
						:custom-messages='{
							duplicateMid: "iqrfnet.networkManager.autoNetwork.midModal.errors.duplicateMid",
							regex: "iqrfnet.networkManager.autoNetwork.midModal.errors.mid",
							required: "iqrfnet.networkManager.autoNetwork.midModal.errors.mid",
						}'
					>
						<CInput
							v-model='mid'
							:label='$t("iqrfnet.networkManager.autoNetwork.midModal.table.deviceMID")'
							:is-valid='touched ? valid : null'
							:invalid-feedback='$t(errors[0])'
						/>
					</ValidationProvider>
					<ValidationProvider
						v-slot='{errors, touched, valid}'
						:rules='{
							between: useAddress ? {min:1, max:239} : false,
							duplicateAddr: useAddress,
							integer: useAddress,
							required: useAddress,
						}'
						:custom-messages='{
							between: "iqrfnet.networkManager.autoNetwork.midModal.errors.address",
							duplicateAddr: "iqrfnet.networkManager.autoNetwork.midModal.errors.duplicateAddr",
							integer: "iqrfnet.networkManager.autoNetwork.midModal.errors.address",
							required: "iqrfnet.networkManager.autoNetwork.midModal.errors.address",
						}'
					>
						<CInput
							v-model.number='address'
							:label='$t("iqrfnet.networkManager.autoNetwork.midModal.table.deviceAddr")'
							:is-valid='touched ? valid : null'
							:invalid-feedback='$t(errors[0])'
							:disabled='!useAddress'
						>
							<template #prepend-content>
								<CInputCheckbox :checked.sync='useAddress' />
							</template>
						</CInput>
					</ValidationProvider>
					<div style='display: flex; justify-content: flex-end;'>
						<CButton
							color='success'
							:disabled='invalid'
							@click='addToList'
						>
							{{ $t('iqrfnet.networkManager.autoNetwork.midModal.add') }}
						</CButton>
					</div>
				</CForm><hr>
			</ValidationObserver>
			<div v-if='invalid.length > 0'>
				<CDataTable
					:fields='invalidHeaders'
					:items='invalid'
					:pagination='true'
					:items-per-page='5'
					:column-filter='true'
					:sorter='{external: false, resetable: true}'
				>
					<template #line='{item}'>
						<td class='col-2'>
							{{ item.line }}
						</td>
					</template>
				</CDataTable><hr>
			</div>
			<div class='text-muted'>
				<small>{{ $t('iqrfnet.networkManager.autoNetwork.notes.midRule1') }}</small><br>
				<small>{{ $t('iqrfnet.networkManager.autoNetwork.notes.midRule2') }}</small><br>
				<small>{{ $t('iqrfnet.networkManager.autoNetwork.notes.midRule3') }}</small>
			</div>
			<template #footer>
				<CButton
					color='secondary'
					@click='closeModal'
				>
					{{ $t('forms.close') }}
				</CButton>
				<CButton
					color='info'
					@click='exportList'
				>
					{{ $t('forms.export') }}
				</CButton>
				<CButton
					color='danger'
					@click='clearData'
				>
					{{ $t('forms.clear') }}
				</CButton>
			</template>
		</CModal>
	</span>
</template>

<script lang='ts'>
import {Component, Prop, PropSync} from 'vue-property-decorator';
import {CButton, CCol, CDataTable, CIcon, CInput, CInputCheckbox, CModal, CRow} from '@coreui/vue/src';
import {extend, ValidationObserver, ValidationProvider} from 'vee-validate';
import ModalBase from '@/components/ModalBase.vue';

import {cilTrash} from '@coreui/icons';
import {between, integer, regex, required} from 'vee-validate/dist/rules';

import {IAtnwMidErrorList, IAtnwMidList} from '@/interfaces/DaemonApi/Iqmesh/Autonetwork';
import {IField} from '@/interfaces/Coreui';
import {saveAs} from 'file-saver';

/**
 * Autonetwork MID list modal window component
 */
@Component({
	components: {
		CButton,
		CCol,
		CDataTable,
		CIcon,
		CInput,
		CInputCheckbox,
		CModal,
		CRow,
		ValidationObserver,
		ValidationProvider,
	},
	data: () => ({
		cilTrash,
	}),
})
export default class MidList extends ModalBase {
	/**
	 * @property {boolean} activatorDisabled Disables activator
	 */
	@Prop({type: Boolean, default: null}) activatorDisabled!: boolean;

	/**
	 * @property {Array<IAtnwMidList>} _list Valid MID list records
	 */
	@PropSync('list',{type: Array, default: []}) _list!: Array<IAtnwMidList>;

	/**
	 * @property {Array<IAtnwMidErrorList>} _invalid!: Invalid MID list records
	 */
	@PropSync('invalid', {type: Array, default: []}) _invalid!: Array<IAtnwMidErrorList>;

	/**
	 * @var {string} mid Device MID
	 */
	private mid = '';

	/**
	 * @var {boolean} useAddress Use device address
	 */
	private useAddress = false;

	/**
	 * @var {number} address Device address
	 */
	private address = 1;

	/**
	 * @constant {Array<IField>} headers MID list table headers
	 */
	private headers: Array<IField> = [
		{
			label: this.$t('iqrfnet.networkManager.autoNetwork.midModal.table.deviceMID'),
			key: 'deviceMID',
		},
		{
			label: this.$t('iqrfnet.networkManager.autoNetwork.midModal.table.deviceAddr'),
			key: 'deviceAddr',
		},
		{
			label: this.$t('iqrfnet.networkManager.autoNetwork.midModal.table.note'),
			key: 'note',
		},
		{
			key: 'actions',
			label: this.$t('table.actions.title'),
			sorter: false,
			filter: false,
		},
	];

	/**
	 * @constant {Array<IField>} invalidHeaders MID invalid list table headers
	 */
	private invalidHeaders: Array<IField> = [
		{
			label: this.$t('iqrfnet.networkManager.autoNetwork.midModal.invalid.line'),
			key: 'line',
		},
		{
			label: this.$t('iqrfnet.networkManager.autoNetwork.midModal.invalid.content'),
			key: 'content',
		},
		{
			label: this.$t('iqrfnet.networkManager.autoNetwork.midModal.invalid.error'),
			key: 'error',
		},
	];

	/**
	 * Initializes validation rules
	 */
	created(): void {
		extend('between', between);
		extend('duplicateAddr', (addr: number) => {
			for (let i = 0; i < this._list.length; ++i) {
				if (this._list[i].deviceAddr === addr) {
					return false;
				}
			}
			return true;
		});
		extend('duplicateMid', (mid: string) => {
			for (let i = 0; i < this._list.length; ++i) {
				if (this._list[i].deviceMID.toLowerCase() === mid.toLowerCase()) {
					return false;
				}
			}
			return true;
		});
		extend('integer', integer);
		extend('regex', regex);
		extend('required', required);
	}

	/**
	 * Adds a new entry to mid list
	 */
	private addToList(): void {
		const list = this._list.slice();
		const entry: IAtnwMidList = {
			deviceMID: this.mid,
		};
		if (this.useAddress) {
			entry.deviceAddr = this.address;
		}
		list.push(entry);
		this._list = list;
		this.reset();
	}

	/**
	 * Removes entry at index from list
	 * @param {number} idx Index of entry
	 */
	private removeFromList(idx: number): void {
		this._list.splice(idx, 1);
	}

	/**
	 * Exports MID list
	 */
	private exportList(): void {
		const content = this._list.map((entry: IAtnwMidList) => {
			delete entry.showEdit;
			return Object.values(entry).toString();
		}).join('\n');
		const blob = new Blob([content], {type: 'text/csv;encoding:utf-8'});
		saveAs(blob, 'midList.csv');
	}

	/**
	 * Resets inputs, calculates first available address
	 */
	private reset(): void {
		this.mid = '';
		this.useAddress = false;
		const addrs = this._list.filter((item: IAtnwMidList) => item.deviceAddr !== undefined).map((item: IAtnwMidList) => {
			return item.deviceAddr;
		});
		for (let i = 1; i < 240; i++) {
			if (!addrs.includes(i)) {
				this.address = i;
				break;
			}
		}
		(this.$refs.form as InstanceType<typeof ValidationObserver>).reset();
	}

	/**
	 * Clears data lists and inputs
	 */
	private clearData(): void {
		this._list = this._invalid = [];
		this.reset();
	}

	/**
	 * Opens modal from the outside
	 */
	public showModal(): void {
		this.reset();
		this.openModal();
	}
}

</script>