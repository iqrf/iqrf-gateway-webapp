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
	<span>
		<CButton
			color='danger'
			:disabled='(autoAddress || (address < 1 || address > 239 || !Number.isInteger(address)))'
			@click='openModal'
		>
			{{ $t('forms.unbond') }}
		</CButton>
		<CModal
			:show.sync='show'
			color='danger'
			size='lg'
			:close-on-backdrop='false'
			:fade='false'
		>
			<template #header>
				<h5 class='modal-title'>
					{{ $t('iqrfnet.networkManager.bondingManager.modal.unbondTitle') }}
				</h5>
			</template>
			{{ $t('iqrfnet.networkManager.bondingManager.modal.unbondPrompt', {address: address}) }}
			<template #footer>
				<CButton
					color='secondary'
					@click='closeModal'
				>
					{{ $t('forms.cancel') }}
				</CButton>
				<CButton
					color='danger'
					@click='unbond'
				>
					{{ $t('forms.unbond') }}
				</CButton>
			</template>
		</CModal>
	</span>
</template>

<script lang='ts'>
import {Component, Prop} from 'vue-property-decorator';
import {CButton, CModal} from '@coreui/vue/src';
import ModalBase from '@/components/ModalBase.vue';

/**
 * Iqmesh modal component
 */
@Component({
	components: {
		CButton,
		CModal,
	},
})
export default class UnbondModal extends ModalBase {
	/**
	 * @property {number} address Device address
	 */
	@Prop({type: Number, default: 1}) address!: number;

	/**
	 * @property {boolean} autoAddres Automatically selected address
	 */
	@Prop({type: Boolean, default: false}) autoAddress!: boolean;

	/**
	 * Emits event to unbond node
	 */
	private unbond(): void {
		this.closeModal();
		this.$emit('unbond');
	}
}

</script>
