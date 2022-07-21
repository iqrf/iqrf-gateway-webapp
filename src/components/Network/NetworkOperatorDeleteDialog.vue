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
		width='50%'
		persistent
		no-click-animation
	>
		<template #activator='{attrs, on}'>
			<v-list-item
				v-bind='attrs'
				v-on='on'
				@click='showDialog'
			>
				<v-icon dense>
					mdi-delete
				</v-icon>
				{{ $t('network.operators.delete') }}
			</v-list-item>
		</template>
		<v-card>
			<v-card-title>
				{{ $t('network.operators.modal.title') }}
			</v-card-title>
			<v-card-text>
				{{ $t('network.operators.modal.prompt', {name: operator.name}) }}
			</v-card-text>
			<v-card-actions>
				<v-spacer />
				<v-btn
					@click='closeDialog'
				>
					{{ $t('forms.cancel') }}
				</v-btn> <v-btn
					color='error'
					@click='deleteOperator'
				>
					{{ $t('forms.delete') }}
				</v-btn>
			</v-card-actions>
		</v-card>
	</v-dialog>
</template>

<script lang='ts'>
import {Component, Prop} from 'vue-property-decorator';
import DialogBase from '../DialogBase.vue';

import {extendedErrorToast} from '@/helpers/errorToast';

import NetworkOperatorService from '@/services/NetworkOperatorService';

import {AxiosError} from 'axios';
import {IOperator} from '@/interfaces/network';

@Component({})

/**
 * Network operator delete dialog component
 */
export default class NetworkOperatorDeleteDialog extends DialogBase {
	/**
	 * @property {IOperator} operator Network operator
	 */
	@Prop({required: true}) operator!: IOperator;

	/**
	 * Deletes operator
	 */
	private deleteOperator(): void {
		if (!this.operator.id) {
			return;
		}
		this.$store.commit('spinner/SHOW');
		NetworkOperatorService.deleteOperator(this.operator.id)
			.then(() => {
				this.$store.commit('spinner/HIDE');
				this.$toast.success(
					this.$t('network.operators.messages.deleteSuccess', {operator: this.operator.name}).toString()
				);
				this.$emit('closed');
			})
			.catch((err: AxiosError) => {
				const params = {name: this.operator.name};
				extendedErrorToast(err, 'network.operators.messages.deleteFailed', params);
				this.$emit('closed');
			});
	}

	/**
	 * Emits event to close parent menu (temporary workaround) and opens dialog
	 */
	private showDialog(): void {
		this.$emit('close-menu');
		this.openDialog();
	}
}
</script>
