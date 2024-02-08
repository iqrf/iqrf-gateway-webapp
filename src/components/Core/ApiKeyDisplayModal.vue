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
	<CModal
		:show.sync='show'
		color='success'
		size='lg'
		:centered='true'
		:close-on-backdrop='false'
		:fade='false'
	>
		<template #header>
			<h5 class='modal-title'>
				{{ $t('core.security.apiKey.display.title') }}
			</h5>
		</template>
		<div class='mb-4'>
			{{ $t('core.security.apiKey.display.prompt') }}
		</div>
		<CInput
			:value='apiKey'
			:readonly='true'
		>
			<template #append>
				<CButton
					v-clipboard:copy='apiKey'
					v-clipboard:success='copyMessage'
					color='success'
					size='sm'
				>
					<CIcon :content='cilClipboard' size='sm' />
					<span class='d-none d-lg-inline'>
						{{ $t('forms.clipboardCopy') }}
					</span>
				</CButton>
			</template>
		</CInput>
		<template #footer>
			<CButton
				class='mr-1'
				color='secondary'
				@click='hideModal'
			>
				{{ $t('forms.close') }}
			</CButton>
		</template>
	</CModal>
</template>

<script lang='ts'>
import {Component, Prop} from 'vue-property-decorator';
import {CButton, CModal} from '@coreui/vue/src';
import ModalBase from '@/components/ModalBase.vue';

import {cilClipboard} from '@coreui/icons';

@Component({
	components: {
		CButton,
		CModal,
	},
	data: () => ({
		cilClipboard,
	}),
})
export default class ApiKeyDisplayModal extends ModalBase {

	/**
	 * @property {string} apiKey Generate API key
	 */
	@Prop({required: true, default: null}) apiKey: string|null = null;

	/**
	 * On clipboard copy toast message
	 */
	private copyMessage(): void {
		this.$toast.success(
			this.$t('core.security.apiKey.display.copyMessage').toString()
		);
	}

	/**
	 * Show modal window
	 */
	public showModal(): void {
		this.show = true;
	}

	/**
	 * Hide modal windows
	 */
	private hideModal(): void {
		this.closeModal();
		this.$emit('closed');
	}
}
</script>
