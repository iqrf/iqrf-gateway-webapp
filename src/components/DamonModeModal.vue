<template>
	<CModal
		:show.sync='daemonModeModal'
		color='warning'
	>
		<template #header>
			<h5 class='modal-title'>
				{{ $t('daemonMode.title') }}
			</h5>
		</template>
		{{ $t('daemonMode.prompt') }}
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
import {Component, Vue} from 'vue-property-decorator';
import {CButton, CModal} from '@coreui/vue/src';
import { mapGetters } from 'vuex';

@Component({
	components: {
		CButton,
		CModal,
	},
	computed: {
		...mapGetters({
			daemonModeModal: 'daemonModeModal',
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
		this.$store.dispatch('hideDaemonModal');
	}
}
</script>
