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
	<div>
		<h5>{{ $t('network.operators.title') }}</h5>
		<v-dialog
			v-model='show'
			width='50%'
			persistent
			no-click-animation
		>
			<template #activator='{attrs, on}'>
				<v-btn
					color='primary'
					v-bind='attrs'
					v-on='on'
					@click='openModal'
				>
					{{ $t('network.operators.browse') }}
				</v-btn>
			</template>
			<v-card>
				<v-card-title>
					{{ $t('network.operators.title') }}
					<v-spacer />
					<v-btn
						class='mr-1'
						color='success'
						small
						@click='operatorFormModel = {name: "", apn: "", username: "", password: ""}'
					>
						<v-icon small>
							mdi-plus
						</v-icon>
					</v-btn>
					<v-btn
						color='primary'
						small
						@click='getOperators'
					>
						<v-icon small>
							mdi-refresh
						</v-icon>
					</v-btn>
				</v-card-title>
				<v-card-text>
					<v-data-table
						:loading='loading'
						:headers='headers'
						:items='operators'
					>
						<template #[`item.actions`]='{item}'>
							<v-btn
								v-if='allowSet'
								class='mr-1'
								color='primary'
								small
								@click='setOperator(item)'
							>
								<v-icon small>
									mdi-copy
								</v-icon>
								{{ $t('network.operators.set') }}
							</v-btn>
							<v-btn
								class='mr-1'
								color='info'
								small
								@click='operatorFormModel = item'
							>
								<v-icon small>
									mdi-pencil
								</v-icon>
								{{ $t('table.actions.edit') }}
							</v-btn>
							<v-btn
								color='error'
								small
								@click='operatorDeleteModel = item'
							>
								<v-icon small>
									mdi-delete
								</v-icon>
								{{ $t('table.actions.delete') }}
							</v-btn>
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
		<NetworkOperatorForm
			v-model='operatorFormModel'
			@saved='getOperators'
		/>
		<NetworkOperatorDeleteModal
			v-model='operatorDeleteModel'
			@deleted='getOperators'
		/>
	</div>
</template>

<script lang='ts'>
import {Component, Prop} from 'vue-property-decorator';
import ModalBase from '@/components/ModalBase.vue';
import NetworkOperatorDeleteModal from '@/components/Network/NetworkOperatorDeleteModal.vue';
import NetworkOperatorForm from '@/components/Network/NetworkOperatorForm.vue';

import {extendedErrorToast} from '@/helpers/errorToast';

import {AxiosError} from 'axios';
import {DataTableHeader} from 'vuetify';
import {
	MobileOperator
} from '@iqrf/iqrf-gateway-webapp-client/types/Network';
import {useApiClient} from '@/services/ApiClient';

/**
 * Network operators components
 */
@Component({
	components: {
		NetworkOperatorDeleteModal,
		NetworkOperatorForm,
	},
})
export default class NetworkOperators extends ModalBase {

	/**
	 * @property
	 */
	@Prop({required: false, default: false}) allowSet!: boolean;

	/**
	 * @var {boolean} loading Data loading
	 */
	private loading = false;

	/**
	 * @var {Array<MobileOperator>} operators Array of network operators
	 */
	private operators: Array<MobileOperator> = [];

	/**
	 * @var {MobileOperator|null} operatorFormModel Operator add/edit form model
	 */
	private operatorFormModel: MobileOperator|null = null;

	/**
	 * @var {MobileOperator|null} operatorDeleteModel Operator to delete model
	 */
	private operatorDeleteModel: MobileOperator|null = null;

	/**
	 * @property {NetworkOperatorService} service Network operator service
	 */
	private service = useApiClient().getNetworkServices().getMobileOperatorService();

	/**
	 * @constant {Array<DataTableHeader>}
	 */
	private readonly headers: Array<DataTableHeader> = [
		{
			value: 'name',
			text: this.$t('network.operators.form.name').toString()
		},
		{
			value: 'actions',
			text: this.$t('table.actions.title').toString(),
			sortable: false,
			filterable: false,
			align: 'end'
		},
	];

	/**
	 * Retrieves operators
	 */
	mounted(): void {
		this.getOperators();
	}

	/**
	 * Retrieves list of network operators
	 */
	private getOperators(): Promise<void> {
		this.loading = true;
		return this.service.list()
			.then((operators: MobileOperator[]) => {
				this.operators = operators;
				this.loading = false;
			})
			.catch((err: AxiosError) => {
				this.loading = false;
				extendedErrorToast(err, 'network.operators.messages.listFailed');
			});
	}

	/**
	 * Sets network operator to configuration form
	 */
	private setOperator(operator: MobileOperator): void {
		this.closeModal();
		this.$emit('set-operator', operator);
	}
}
</script>
