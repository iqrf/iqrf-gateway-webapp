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
				color='error'
				v-bind='attrs'
				v-on='on'
				@click='openModal'
			>
				{{ $t('forms.clearBonds') }}
			</v-btn>
		</template>
		<v-card>
			<v-card-title>
				{{ $t('iqrfnet.networkManager.bondingManager.modal.clearAllTitle') }}
			</v-card-title>
			<v-card-text>
				{{ $t('iqrfnet.networkManager.bondingManager.modal.clearAllPrompt') }}
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
					@click='clear'
				>
					{{ $t('forms.clearBonds') }}
				</v-btn>
			</v-card-actions>
		</v-card>
	</v-dialog>
</template>

<script lang='ts'>
import {Component} from 'vue-property-decorator';
import ModalBase from '@/components/ModalBase.vue';

@Component
export default class ClearAllModal extends ModalBase {
	/**
	 * Emits event to clear all bonds
	 */
	private clear(): void {
		this.closeModal();
		this.$emit('clear');
	}
}
</script>
