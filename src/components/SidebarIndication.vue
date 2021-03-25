<template>
	<table 
		v-if='!$store.getters["sidebar/isMinimized"]'
		class='table'
	>
		<tbody>
			<tr>
				<td class='item'>
					{{ $t('daemonStatus.mode') }}
				</td>
				<td class='status'>
					<CBadge :color='daemonModeBadgeColor'>
						{{ $t('daemonStatus.modes.' + (isSocketConnected ? daemonStatus.mode : 'unknown')) }}
					</CBadge>
				</td>
			</tr>
			<tr>
				<td class='item'>
					{{ $t('daemonStatus.websocket.title') }}
				</td>
				<td class='status'>
					<CBadge :color='isSocketConnected ? "success": "danger"'>
						{{ $t('daemonStatus.websocket.' + (isSocketConnected ? 'connected' : 'notConnected')) }}
					</CBadge>
				</td>
			</tr>
			<tr>
				<td class='item'>
					{{ $t('daemonStatus.queue') }}
				</td>
				<td class='status'>
					<CBadge :color='daemonQueueBadgeColor'>
						{{ 'Length: ' + daemonStatus.queueLen }}
					</CBadge>
				</td>
			</tr>
		</tbody>
	</table>
</template>

<script lang='ts'>
import {Component, Vue} from 'vue-property-decorator';
import {CBadge} from '@coreui/vue/src';

import {mapGetters} from 'vuex';

@Component({
	components: {
		CBadge
	},
	computed: {
		...mapGetters({
			daemonStatus: 'daemonStatus',
			isSocketConnected: 'isSocketConnected',
		}),
	},
})

/**
 * Sidebar indication component
 */
export default class SidebarIndication extends Vue {

	/**
	 * Computes Daemon mode badge color
	 * @returns {string} Daemon mode badge color
	 */
	get daemonModeBadgeColor(): string {
		const daemonStatus = this.$store.getters.daemonStatus;
		const socketConnected = this.$store.getters.isSocketConnected;
		if (!socketConnected) {
			return 'secondary';
		}
		if (daemonStatus.mode === 'unknown') {
			return 'secondary';
		} else if (daemonStatus.mode === 'operational' ||
			daemonStatus.mode === 'forwarding') {
			return 'success';
		} else {
			return 'danger';
		}
	}

	/**
	 * Computes Daemon queue length badge color
	 * @returns {string} Daemon queue length badge color
	 */
	get daemonQueueBadgeColor(): string {
		const daemonStatus = this.$store.getters.daemonStatus;
		if (daemonStatus.queueLen <= 16) {
			return 'success';
		} else if (daemonStatus.queueLen <= 24) {
			return 'warning';
		} else {
			return 'danger';
		}
	}
}
</script>

<style scoped>
table {
	color: white;
	margin-bottom: 0.5rem;
}

.item {
	border-top: 0;
	text-align: left;
	padding: 0;
	padding-left: 1.25rem;
}

.status {
	border-top: 0;
	text-align: right;
	padding: 0;
	padding-right: 1.25rem
}
</style>