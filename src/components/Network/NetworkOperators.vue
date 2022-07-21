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
					<NetworkOperatorFormDialog @saved='getOperators' />
					<v-menu
						v-for='(operator, i) of operators'
						:key='i'
						ref='menu'
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
								{{ operator.name }}
								<v-icon color='white'>
									mdi-menu-up
								</v-icon>
							</v-btn>
						</template>
						<v-list dense>
							<v-list-item
								@click='$emit("apply", operator)'
							>
								<v-icon dense>
									mdi-content-copy
								</v-icon>
								{{ $t('network.operators.apply') }}
							</v-list-item>
							<NetworkOperatorFormDialog
								:operator='operator'
								@saved='getOperators'
								@close-menu='$refs.menu[i].isActive = false'
							/>
							<NetworkOperatorDeleteDialog
								:operator='operator'
								@deleted='getOperators'
								@close-menu='$refs.menu[i].isActive = false'
							/>
						</v-list>
					</v-menu>
				</v-btn-toggle>
			</v-card-text>
		</v-card>
	</div>
</template>

<script lang='ts'>
import {Component, Vue} from 'vue-property-decorator';
import NetworkOperatorDeleteDialog from './NetworkOperatorDeleteDialog.vue';
import NetworkOperatorFormDialog from './NetworkOperatorFormDialog.vue';

import {extendedErrorToast} from '@/helpers/errorToast';

import NetworkOperatorService from '@/services/NetworkOperatorService';

import {AxiosError, AxiosResponse} from 'axios';
import {IOperator} from '@/interfaces/network';


@Component({
	components: {
		NetworkOperatorDeleteDialog,
		NetworkOperatorFormDialog,
	},
})

/**
 * Network operators components
 */
export default class NetworkOperators extends Vue {
	/**
	 * @var {Array<IOperator>} operators Array of network operators
	 */
	private operators: Array<IOperator> = [];

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
				const operators: Array<IOperator> = rsp.data;
				this.operators = operators.map((operator: IOperator) => {
					if (!operator.username) {
						operator.username = '';
					}
					if (!operator.password) {
						operator.password = '';
					}
					return operator;
				});
			})
			.catch((err: AxiosError) => extendedErrorToast(err, 'network.operators.messages.listFailed'));
	}

	/**
	 *
	 */
	private handleCloseMenu(idx: number): void {
		(this.$refs['menu']![idx] as any).isActive = false;
	}
}
</script>
