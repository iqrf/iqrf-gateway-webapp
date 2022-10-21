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
	<div class='form-group'>
		<h4>{{ $t('network.operators.title') }}</h4>
		<CButtonGroup class='flex-wrap'>
			<CButton
				color='success'
				size='sm'
				@click='addOperator'
			>
				<CIcon :content='cilPlus' />
			</CButton>
			<CDropdown
				v-for='(operator, i) of operators'
				:key='i'
				:toggler-text='operator.getName()'
				color='primary'
				placement='top-start'
			>
				<CDropdownItem
					v-if='$route.path.includes("network/mobile/add") || $route.path.includes("network/mobile/edit")'
					@click='$emit("apply", operator)'
				>
					<CIcon :content='cilCopy' />
					{{ $t('network.operators.apply') }}
				</CDropdownItem>
				<CDropdownItem
					@click='editOperator(i)'
				>
					<CIcon :content='cilPencil' />
					{{ $t('network.operators.edit') }}
				</CDropdownItem>
				<CDropdownItem
					@click='deleteOperator(i)'
				>
					<CIcon :content='cilTrash' />
					{{ $t('network.operators.delete') }}
				</CDropdownItem>
			</CDropdown>
		</CButtonGroup>
		<NetworkOperatorDeleteModal ref='operatorDelete' @closed='getOperators' />
		<NetworkOperatorForm ref='operatorForm' @closed='getOperators' />
	</div>
</template>

<script lang='ts'>
import {Component, Vue} from 'vue-property-decorator';
import {
	CButton,
	CButtonGroup,
	CDropdown,
	CDropdownItem,
	CIcon,
	CModal
} from '@coreui/vue/src';
import {cilCopy, cilPencil, cilPlus, cilTrash} from '@coreui/icons';
import NetworkOperatorDeleteModal from './NetworkOperatorDeleteModal.vue';
import NetworkOperatorForm from './NetworkOperatorForm.vue';

import {extendedErrorToast} from '@/helpers/errorToast';

import NetworkOperator from '@/entities/NetworkOperator';

import NetworkOperatorService from '@/services/NetworkOperatorService';

import {AxiosError, AxiosResponse} from 'axios';
import {IOperator} from '@/interfaces/Network/Mobile';


@Component({
	components: {
		CButton,
		CButtonGroup,
		CDropdown,
		CDropdownItem,
		CIcon,
		CModal,
		NetworkOperatorDeleteModal,
		NetworkOperatorForm,
	},
	data: () => ({
		cilCopy,
		cilPencil,
		cilPlus,
		cilTrash,
	}),
})

/**
 * Network operators components
 */
export default class NetworkOperators extends Vue {

	/**
	 * @var {Array<IOperator>} operators Array of network operators
	 */
	private operators: Array<NetworkOperator> = [];

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
		return NetworkOperatorService.getOperators()
			.then((rsp: AxiosResponse) => {
				const operators: Array<NetworkOperator> = [];
				rsp.data.forEach((operator: IOperator) => {
					operators.push(new NetworkOperator(operator));
				});
				this.operators = operators;
			})
			.catch((err: AxiosError) => extendedErrorToast(err, 'network.operators.messages.listFailed'));
	}

	/**
	 * Activates network operator form modal window to add new operator
	 */
	private addOperator(): void {
		(this.$refs.operatorForm as NetworkOperatorForm).activateModal();
	}

	/**
	 * Activates network operator form modal window to edit existing operator
	 * @param {number} idx Operator index
	 */
	private editOperator(idx: number): void {
		const id = this.operators[idx].getId();
		(this.$refs.operatorForm as NetworkOperatorForm).activateModal(id);
	}

	/**
	 * Deletes operator
	 * @param {number} idx Operator index
	 */
	private deleteOperator(idx: number): void {
		const id = this.operators[idx].getId();
		const name = this.operators[idx].getName();
		(this.$refs.operatorDelete as NetworkOperatorDeleteModal).activateModal(id, name);
	}
}
</script>
