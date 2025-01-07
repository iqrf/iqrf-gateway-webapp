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
	<v-dialog
		v-model='show'
		width='50%'
		persistent
		no-click-animation
	>
		<template #activator='{attrs, on}'>
			<v-btn
				color='primary'
				small
				:disabled='activatorDisabled'
				v-bind='attrs'
				v-on='on'
				@click='openModal'
			>
				{{ $t('iqrfnet.networkManager.autoNetwork.form.bondingControl.mid.manual') }}
			</v-btn>
		</template>
		<v-card>
			<v-card-title>
				{{ $t('iqrfnet.networkManager.autoNetwork.midModal.title') }}
			</v-card-title>
			<v-card-text>
				<v-data-table
					:headers='headers'
					:items='list'
					mobile-breakpoint='0'
					:no-data-text='$t("iqrfnet.networkManager.autoNetwork.midModal.table.noItems")'
				>
					<template #[`item.deviceAddr`]='{item}'>
						{{ item.deviceAddr ? item.deviceAddr : $t('iqrfnet.networkManager.autoNetwork.midModal.table.unspecified') }}
					</template>
					<template #[`item.note`]='{item}'>
						{{ item.note ? item.note.split(';').join(', ') : $t('iqrfnet.networkManager.autoNetwork.midModal.table.unspecified') }}
					</template>
					<template #[`item.actions`]='{item}'>
						<v-btn
							color='error'
							small
							@click='removeFromList(list.indexOf(item))'
						>
							<v-icon small>
								mdi-delete
							</v-icon>
						</v-btn>
					</template>
				</v-data-table>
				<v-divider class='mb-2' />
				<ValidationObserver v-slot='{invalid}' ref='form'>
					<v-form>
						<v-row>
							<v-col cols='12' md='6'>
								<ValidationProvider
									v-slot='{errors, touched, valid}'
									:rules='{
										duplicateMid: true,
										regex: /^[0-9a-fA-F]{8}$/,
										required: true,
									}'
									:custom-messages='{
										duplicateMid: $t("iqrfnet.networkManager.autoNetwork.midModal.errors.duplicateMid"),
										regex: $t("iqrfnet.networkManager.autoNetwork.midModal.errors.mid"),
										required: $t("iqrfnet.networkManager.autoNetwork.midModal.errors.mid"),
									}'
								>
									<v-text-field
										v-model='mid'
										:label='$t("iqrfnet.networkManager.autoNetwork.midModal.table.deviceMID")'
										:success='touched ? valid : null'
										:error-messages='errors'
									/>
								</ValidationProvider>
							</v-col>
							<v-col cols='12' md='6'>
								<ValidationProvider
									v-slot='{errors, touched, valid}'
									:rules='{
										between: useAddress ? {min:1, max:239} : false,
										duplicateAddr: useAddress,
										integer: useAddress,
										required: useAddress,
									}'
									:custom-messages='{
										between: $t("iqrfnet.networkManager.autoNetwork.midModal.errors.address"),
										duplicateAddr: $t("iqrfnet.networkManager.autoNetwork.midModal.errors.duplicateAddr"),
										integer: $t("iqrfnet.networkManager.autoNetwork.midModal.errors.address"),
										required: $t("iqrfnet.networkManager.autoNetwork.midModal.errors.address"),
									}'
								>
									<v-text-field
										v-model.number='address'
										:label='$t("iqrfnet.networkManager.autoNetwork.midModal.table.deviceAddr")'
										:success='touched ? valid : null'
										:error-messages='errors'
										:disabled='!useAddress'
									>
										<template #prepend>
											<v-checkbox
												v-model='useAddress'
												color='primary'
											/>
										</template>
										<template #append-outer>
											<v-btn
												color='success'
												small
												:disabled='invalid'
												@click='addToList'
											>
												<v-icon small>
													mdi-plus
												</v-icon>
											</v-btn>
										</template>
									</v-text-field>
								</ValidationProvider>
							</v-col>
						</v-row>
					</v-form>
				</ValidationObserver>
				<v-divider />
				<div class='text-muted'>
					<small>{{ $t('iqrfnet.networkManager.autoNetwork.notes.midRule1') }}</small><br>
					<small>{{ $t('iqrfnet.networkManager.autoNetwork.notes.midRule2') }}</small><br>
					<small>{{ $t('iqrfnet.networkManager.autoNetwork.notes.midRule3') }}</small>
				</div>
				<v-divider />
				<v-data-table
					v-if='invalid.length > 0'
					:headers='invalidHeaders'
					:items='invalid'
					mobile-breakpoint='0'
				>
					<template #[`item.line`]='{item}'>
						{{ item.line }}
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
				<v-btn
					color='info'
					@click='exportList'
				>
					{{ $t('forms.export') }}
				</v-btn>
				<v-btn
					color='error'
					@click='clearData'
				>
					{{ $t('forms.clear') }}
				</v-btn>
			</v-card-actions>
		</v-card>
	</v-dialog>
</template>

<script lang='ts'>
import {Component, Prop, PropSync, Ref} from 'vue-property-decorator';
import {extend, ValidationObserver, ValidationProvider} from 'vee-validate';
import ModalBase from '@/components/ModalBase.vue';

import {between, integer, regex, required} from 'vee-validate/dist/rules';
import {saveAs} from 'file-saver';

import {IAtnwMidErrorList, IAtnwMidList} from '@/interfaces/DaemonApi/Iqmesh/Autonetwork';
import {DataTableHeader} from 'vuetify';

/**
 * Autonetwork MID list modal window component
 */
@Component({
	components: {
		ValidationObserver,
		ValidationProvider,
	},
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

	@Ref('form') readonly form!: InstanceType<typeof ValidationObserver>;

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
	 * @constant {Array<DataTableHeader>} headers MID list table headers
	 */
	private headers: Array<DataTableHeader> = [
		{
			text: this.$t('iqrfnet.networkManager.autoNetwork.midModal.table.deviceMID').toString(),
			value: 'deviceMID',
		},
		{
			text: this.$t('iqrfnet.networkManager.autoNetwork.midModal.table.deviceAddr').toString(),
			value: 'deviceAddr',
		},
		{
			text: this.$t('iqrfnet.networkManager.autoNetwork.midModal.table.note').toString(),
			value: 'note',
		},
		{
			value: 'actions',
			text: this.$t('table.actions.title').toString(),
			sortable: false,
			filterable: false,
			align: 'end',
		},
	];

	/**
	 * @constant {Array<DataTableHeader>} invalidHeaders MID invalid list table headers
	 */
	private invalidHeaders: Array<DataTableHeader> = [
		{
			text: this.$t('iqrfnet.networkManager.autoNetwork.midModal.invalid.line').toString(),
			value: 'line',
		},
		{
			text: this.$t('iqrfnet.networkManager.autoNetwork.midModal.invalid.content').toString(),
			value: 'content',
		},
		{
			text: this.$t('iqrfnet.networkManager.autoNetwork.midModal.invalid.error').toString(),
			value: 'error',
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
		this.form.reset();
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
		this.openModal();
	}
}

</script>
