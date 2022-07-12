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
	<div>
		<v-card>
			<v-card-title>{{ $t('network.operators.title') }}</v-card-title>
			<v-card-text>
				<v-btn-toggle class='flex-wrap' dense>
					<v-btn
						color='success'
						small
						@click='addOperator'
					>
						<v-icon small>
							mdi-plus
						</v-icon>
					</v-btn>
					<v-menu
						v-for='(operator, i) of operators'
						:key='i'
						offset-y
						top
					>
						<template #activator='{on, attrs}'>
							<v-btn
								v-bind='attrs'
								color='primary'
								small
								v-on='on'
							>
								{{ operator.getName() }}
								<v-icon color='white'>
									mdi-menu-up
								</v-icon>
							</v-btn>
						</template>
						<v-list dense>
							<v-list-item
								v-if='$route.path.includes("network/mobile/add") || $route.path.includes("network/mobile/edit")'
								@click='$emit("apply", operator)'
							>
								<v-icon dense>
									mdi-content-copy
								</v-icon>
								{{ $t('network.operators.apply') }}
							</v-list-item>
							<v-list-item
								@click='editOperator(i)'
							>
								<v-icon dense>
									mdi-pencil
								</v-icon>
								{{ $t('network.operators.edit') }}
							</v-list-item>
							<v-list-item
								@click='deleteOperator(i)'
							>
								<v-icon dense>
									mdi-delete
								</v-icon>
								{{ $t('network.operators.delete') }}
							</v-list-item>
						</v-list>
					</v-menu>
				</v-btn-toggle>
			</v-card-text>
		</v-card>
		<NetworkOperatorDeleteModal ref='operatorDelete' @closed='getOperators' />
		<NetworkOperatorForm ref='operatorForm' @closed='getOperators' />
	</div>
</template>

<script lang='ts'>
import {Component, Vue} from 'vue-property-decorator';
import NetworkOperatorDeleteModal from './NetworkOperatorDeleteModal.vue';
import NetworkOperatorForm from './NetworkOperatorForm.vue';

import {extendedErrorToast} from '@/helpers/errorToast';

import NetworkOperator from '@/entities/NetworkOperator';

import NetworkOperatorService from '@/services/NetworkOperatorService';

import {AxiosError, AxiosResponse} from 'axios';
import {IOperator} from '@/interfaces/network';


@Component({
	components: {
		NetworkOperatorDeleteModal,
		NetworkOperatorForm,
	},
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
