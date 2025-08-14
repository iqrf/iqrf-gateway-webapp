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
	<span>
		<CButton
			color='danger'
			@click='openModal'
		>
			{{ $t('forms.clearBonds') }}
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
					{{ $t('iqrfnet.networkManager.bondingManager.modal.clearAllTitle') }}
				</h5>
			</template>
			{{ $t('iqrfnet.networkManager.bondingManager.modal.clearAllPrompt') }}
			<template #footer>
				<CButton
					color='secondary'
					@click='closeModal'
				>
					{{ $t('forms.cancel') }}
				</CButton>
				<CButton
					color='danger'
					@click='clear'
				>
					{{ $t('forms.clearBonds') }}
				</CButton>
			</template>
		</CModal>
	</span>
</template>

<script lang='ts'>
import {Component} from 'vue-property-decorator';
import {CButton, CModal} from '@coreui/vue/src';
import ModalBase from '@/components/ModalBase.vue';

@Component({
	components: {
		CButton,
		CModal,
	}
})
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
