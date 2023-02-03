<!--
Copyright 2017-2023 IQRF Tech s.r.o.
Copyright 2019-2023 MICRORISC s.r.o.

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
		color='danger'
		:show.sync='show'
		:close-on-backdrop='false'
	>
		<template #header>
			<h5 class='modal-title'>
				{{ $t('network.operators.modal.title') }}
			</h5>
		</template>
		{{ $t('network.operators.modal.prompt', {name: name}) }}
		<template #footer>
			<CButton
				color='danger'
				@click='deleteOperator'
			>
				{{ $t('forms.delete') }}
			</CButton> <CButton
				color='secondary'
				@click='deactivateModal'
			>
				{{ $t('forms.cancel') }}
			</CButton>
		</template>
	</CModal>
</template>

<script lang='ts'>
import {Component, Vue} from 'vue-property-decorator';
import {CButton, CModal} from '@coreui/vue/src';

import {extendedErrorToast} from '@/helpers/errorToast';

import NetworkOperatorService from '@/services/NetworkOperatorService';

import {AxiosError} from 'axios';

@Component({
	components: {
		CButton,
		CModal,
	},
})

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
