<!--
Copyright 2017-2024 IQRF Tech s.r.o.
Copyright 2019-2024 MICRORISC s.r.o.

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
				class='mr-1'
				color='error'
				v-bind='attrs'
				:disabled='(autoAddress || (address < 1 || address > 239 || !Number.isInteger(address)))'
				v-on='on'
				@click='openModal'
			>
				{{ $t('forms.unbond') }}
			</v-btn>
		</template>
		<v-card>
			<v-card-title>
				{{ $t('iqrfnet.networkManager.bondingManager.modal.unbondTitle') }}
			</v-card-title>
			<v-card-text>
				{{ $t('iqrfnet.networkManager.bondingManager.modal.unbondPrompt', {address: address}) }}
			</v-card-text>
			<v-card-actions>
				<v-spacer />
				<v-btn
					@click='closeModal'
				>
					{{ $t('forms.cancel') }}
				</v-btn>
				<v-btn
					color='error'
					@click='unbond'
				>
					{{ $t('forms.unbond') }}
				</v-btn>
			</v-card-actions>
		</v-card>
	</v-dialog>
</template>

<script lang='ts'>
import {Component, Prop} from 'vue-property-decorator';
import ModalBase from '@/components/ModalBase.vue';

/**
 * Iqmesh modal component
 */
@Component
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
