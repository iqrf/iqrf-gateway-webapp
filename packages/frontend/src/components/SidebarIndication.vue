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
	<v-simple-table
		v-if='!$store.getters["sidebar/isMinimized"]'
		dense
	>
		<tbody>
			<tr>
				<td class='item'>
					{{ $t('daemonStatus.mode') }}
				</td>
				<td class='status'>
					<VChip
						:color='daemonModeBadgeColor'
						small
						label
					>
						{{ $t(`daemonStatus.modes.${isSocketConnected ? daemonMode : 'unknown'}`) }}
					</VChip>
				</td>
			</tr>
			<tr>
				<td class='item'>
					{{ $t('daemonStatus.websocket.title') }}
				</td>
				<td class='status'>
					<VChip
						:color='isSocketConnected ? "success": "error"'
						small
						label
						:ripple='false'
					>
						{{ $t(`daemonStatus.websocket.${isSocketConnected ? 'connected' : 'notConnected'}`) }}
					</VChip>
				</td>
			</tr>
			<tr>
				<td class='item'>
					{{ $t('daemonStatus.queue') }}
				</td>
				<td class='status'>
					<VChip
						:color='daemonQueueBadgeColor'
						small
						label
					>
						{{ queueLen }}
					</VChip>
				</td>
			</tr>
		</tbody>
	</v-simple-table>
</template>

<script lang='ts'>
import {Component, Vue} from 'vue-property-decorator';

import {mapGetters} from 'vuex';

@Component({
	computed: {
		...mapGetters({
			daemonMode: 'monitorClient/getMode',
			isSocketConnected: 'daemonClient/isConnected',
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
		const daemonMode = this.$store.getters['monitorClient/getMode'];
		const socketConnected = this.$store.getters['daemonClient/isConnected'];
		if (!socketConnected) {
			return 'secondary';
		}
		if (daemonMode === 'unknown') {
			return 'secondary';
		} else if (daemonMode === 'operational' || daemonMode === 'forwarding') {
			return 'success';
		} else {
			return 'error';
		}
	}

	/**
	 * Computes Daemon queue length badge color
	 * @returns {string} Daemon queue length badge color
	 */
	get daemonQueueBadgeColor(): string {
		const queueLen = this.$store.getters['monitorClient/getQueueLen'];
		const socketConnected = this.$store.getters['daemonClient/isConnected'];
		if (!socketConnected || queueLen === 'unknown') {
			return 'secondary';
		}
		if (queueLen <= 16) {
			return 'success';
		} else if (queueLen <= 24) {
			return 'warning';
		} else {
			return 'error';
		}
	}

	/**
	 * Computes Daemon queue length string
	 * @returns {string} Daemon queue length string
	 */
	get queueLen(): string {
		const queueLen = this.$store.getters['monitorClient/getQueueLen'];
		const socketConnected = this.$store.getters['daemonClient/isConnected'];
		if (!socketConnected || queueLen === 'unknown') {
			return this.$t('daemonStatus.modes.unknown').toString();
		}
		return 'Length: ' + queueLen;
	}
}
</script>

<style scoped lang='scss'>
tbody {
	background-color: rgb(60 75 100 / 60%);

	tr:hover {
		background-color: transparent !important;
	}
}

.item {
	border-top: 0;
	text-align: left;
	padding: 0 0 0 0.5rem;
}

.status {
	border-top: 0;
	text-align: right;
	font-weight: bold;
}
</style>
