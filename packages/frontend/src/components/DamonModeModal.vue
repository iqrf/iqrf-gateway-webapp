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
	<CModal
		:show.sync='modalState'
		color='warning'
	>
		<template #header>
			<h5 class='modal-title'>
				{{ $t('daemonStatus.modalTitle') }}
			</h5>
		</template>
		{{ $t('daemonStatus.modalPrompt') }}
		<template #footer>
			<CButton
				color='warning'
				@click='hideModal()'
			>
				{{ $t('forms.close') }}
			</CButton>
		</template>
	</CModal>
</template>

<script lang='ts'>
import { Component, Vue } from 'vue-property-decorator';
import { CButton, CModal } from '@coreui/vue/src';

import { mapGetters } from 'vuex';

@Component({
	components: {
		CButton,
		CModal,
	},
	computed: {
		...mapGetters({
			modalState: 'monitorClient/getModalState'
		}),
	},
})

/**
 * Daemon mode notice modal window
 */
export default class DaemonModeModal extends Vue {

	/**
	 * Requests to hide modal window
	 */
	private hideModal(): void {
		this.$store.commit('monitorClient/HIDE_MODAL');
	}
}
</script>
