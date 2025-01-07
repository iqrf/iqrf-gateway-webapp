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
		v-model='showModal'
		width='50%'
		persistent
		no-click-animation
	>
		<v-card v-if='operator !== null'>
			<v-card-title>
				{{ $t('network.operators.modal.title') }}
			</v-card-title>
			<v-card-text>
				{{ $t('network.operators.modal.prompt', {name: operator.name}) }}
			</v-card-text>
			<v-card-actions>
				<v-spacer />
				<v-btn
					@click='hideModal'
				>
					{{ $t('forms.cancel') }}
				</v-btn>
				<v-btn
					color='error'
					@click='remove'
				>
					{{ $t('forms.delete') }}
				</v-btn>
			</v-card-actions>
		</v-card>
	</v-dialog>
</template>

<script lang='ts'>
import {Component, VModel, Vue} from 'vue-property-decorator';

import {extendedErrorToast} from '@/helpers/errorToast';

import {AxiosError} from 'axios';
import {
	MobileOperator
} from '@iqrf/iqrf-gateway-webapp-client/types/Network';
import {useApiClient} from '@/services/ApiClient';

/**
 * Network operator delete modal component
 */
@Component
export default class NetworkOperatorDeleteModal extends Vue {

	/**
	 * @property {MobileOperator|null} operator Operator to delete
	 */
	@VModel({required: true}) operator!: MobileOperator|null;

	/**
	 * @property {NetworkOperatorService} service Network operator service
	 */
	private service = useApiClient().getNetworkServices().getMobileOperatorService();

	/**
	 * Computes modal display condition
	 */
	get showModal(): boolean {
		return this.operator !== null;
	}

	/**
	 * Deletes operator
	 */
	private remove(): void {
		if (this.operator === null || this.operator.id === undefined) {
			return;
		}
		const name = this.operator.name;
		this.$store.commit('spinner/SHOW');
		this.service.delete(this.operator.id)
			.then(() => {
				this.$store.commit('spinner/HIDE');
				this.$toast.success(
					this.$t('network.operators.messages.deleteSuccess', {operator: name}).toString()
				);
				this.hideModal();
				this.$emit('deleted');
			})
			.catch((err: AxiosError) => {
				extendedErrorToast(err, 'network.operators.messages.deleteFailed', {name: name});
			});
	}

	/**
	 * Hides modal window
	 */
	private hideModal(): void {
		this.operator = null;
	}
}
</script>
