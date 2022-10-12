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
	<CModal
		color='danger'
		size='lg'
		:show.sync='show'
		:close-on-backdrop='false'
		:fade='false'
	>
		<template #header>
			<h5 class='modal-title'>
				{{ $t('config.controller.deleteModal.title') }}
			</h5>
		</template>
		{{ $t('config.controller.deleteModal.prompt', {profile: name}) }}
		<template #footer>
			<CButton
				class='mr-1'
				color='secondary'
				@click='deactivateModal'
			>
				{{ $t('forms.cancel') }}
			</CButton>
			<CButton
				color='danger'
				@click='deleteProfile'
			>
				{{ $t('forms.delete') }}
			</CButton>
		</template>
	</CModal>
</template>

<script lang='ts'>
import {Component, Vue} from 'vue-property-decorator';
import {CButton, CModal} from '@coreui/vue/src';

@Component({
	components: {
		CButton,
		CModal,
	},
})

/**
 * Controller pin configuration delete modal window component
 */
export default class ControllerPinConfigDeleteModal extends Vue {
	/**
	 * @var {boolean} show Controls whether modal window is rendered
	 */
	private show = false;

	/**
	 * @var {number} idx Controller pin configuration profile index
	 */
	private idx = 0;

	/**
	 * @var {string} name Controller pin configuration profile name
	 */
	private name = '';

	/**
	 * Stores controller pin configuration profile metadata and renders the modal window
	 * @param {number} idx Profile index
	 * @param {string} name Profile name
	 */
	public activateModal(idx: number, name: string): void {
		this.idx = idx;
		this.name = name;
		this.show = true;
	}

	/**
	 * Emits event to delete controller pin configuration profile
	 */
	private deleteProfile(): void {
		this.deactivateModal();
		this.$emit('delete-profile', this.idx);
	}

	/**
	 * Clears controller pin configuration profile metadata and closes the modal window
	 */
	private deactivateModal(): void {
		this.show = false;
	}
}
</script>
