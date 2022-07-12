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
		width='auto'
		persistent
		no-click-animation
	>
		<v-card>
			<v-card-title class='text-h5 error'>
				{{ $t('network.operators.modal.title') }}
			</v-card-title>
			<v-card-text>
				{{ $t('network.operators.modal.prompt', {name: name}) }}
			</v-card-text>
			<v-card-actions>
				<v-spacer />
				<v-btn
					color='error'
					@click='deleteOperator'
				>
					{{ $t('forms.delete') }}
				</v-btn> <v-btn
					color='secondary'
					@click='deactivateModal'
				>
					{{ $t('forms.cancel') }}
				</v-btn>
			</v-card-actions>
		</v-card>
	</v-dialog>
</template>

<script lang='ts'>
import {Component, Vue} from 'vue-property-decorator';

import {extendedErrorToast} from '@/helpers/errorToast';

import NetworkOperatorService from '@/services/NetworkOperatorService';

import {AxiosError} from 'axios';

@Component({})

/**
 * Network operator delete modal component
 */
export default class NetworkOperatorDeleteModal extends Vue {

	/**
	 * @var {boolean} show Controls whether modal is displayed
	 */
	private show = false;

	/**
	 * @var {number} id Operator ID
	 */
	private id = 0;

	/**
	 * @var {string} name Operator name
	 */
	private name = '';

	/**
	 * Shows the modal window
	 * @param {number} id Operator ID
	 * @param {string} name Operator name
	 */
	public activateModal(id: number, name: string): void {
		this.id = id;
		this.name = name;
		this.show = true;
	}

	/**
	 * Hides the modal window
	 */
	public deactivateModal(): void {
		this.id = 0;
		this.name = '';
		this.show = false;
	}

	/**
	 * Deletes operator
	 */
	private deleteOperator(): void {
		this.$store.commit('spinner/SHOW');
		NetworkOperatorService.deleteOperator(this.id)
			.then(() => {
				this.$store.commit('spinner/HIDE');
				this.$toast.success(
					this.$t('network.operators.messages.deleteSuccess', {operator: this.name}).toString()
				);
				this.deactivateModal();
				this.$emit('closed');
			})
			.catch((err: AxiosError) => {
				const params = {name: this.name};
				extendedErrorToast(err, 'network.operators.messages.deleteFailed', params);
				this.deactivateModal();
				this.$emit('closed');
			});
	}
}
</script>
