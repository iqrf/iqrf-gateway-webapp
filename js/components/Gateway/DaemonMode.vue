<template>
	<CCard body-wrapper>
		<table class='table table-striped'>
			<tr>
				<th>{{ $t('gateway.info.gwMode') }}</th>
				<td>
					{{ $t('gateway.info.gwModes.' + mode) }}
				</td>
			</tr>
		</table>
		<div v-if='mode !== "unknown"'>
			<CButton color='primary' @click='setMode("operational")'>
				{{ $t('gateway.mode.modes.operational.title') }}
			</CButton>
			<CButton color='primary' @click='setMode("service")'>
				{{ $t('gateway.mode.modes.service.title') }}
			</CButton>
			<CButton color='primary' @click='setMode("forwarding")'>
				{{ $t('gateway.mode.modes.forwarding.title') }}
			</CButton>
		</div>
	</CCard>
</template>

<script>
import {CButton, CCard} from '@coreui/vue';
import DaemonModeService from '../../services/DaemonModeService';

export default {
	name: 'DaemonMode',
	components: {
		CButton,
		CCard,
	},
	data() {
		return {
			hasData: false,
			mode: 'unknown',
		};
	},
	created() {
		this.unsubscribe = this.$store.subscribe(mutation => {
			if (mutation.type === 'SOCKET_ONOPEN') {
				DaemonModeService.get();
				return;
			}
			if (mutation.type !== 'SOCKET_ONMESSAGE' ||
					mutation.payload.mType !== 'mngDaemon_Mode') {
				return;
			}
			try {
				this.mode = mutation.payload.data.rsp.operMode;
			} catch (e) {
				this.mode = 'unknown';
			}
		});
		if (this.$store.state.webSocketClient.socket.isConnected) {
			DaemonModeService.get();
		}
	},
	beforeDestroy() {
		this.unsubscribe();
	},
	methods: {
		setMode(newMode) {
			DaemonModeService.set(newMode);
		},
	},
};
</script>
