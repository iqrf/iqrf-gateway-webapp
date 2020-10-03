<template>
	<CCard body-wrapper>
		<table class='table table-striped'>
			<tr>
				<th>{{ $t('gateway.info.gwMode') }}</th>
				<td>{{ $t('gateway.mode.modes.' + mode) }}</td>
			</tr>
		</table>
		<div v-if='mode !== "unknown"'>
			<CButton color='primary' @click='setMode("operational")'>
				{{ $t('gateway.mode.modes.operational') }}
			</CButton>
			<CButton color='primary' @click='setMode("service")'>
				{{ $t('gateway.mode.modes.service') }}
			</CButton>
			<CButton color='primary' @click='setMode("forwarding")'>
				{{ $t('gateway.mode.modes.forwarding') }}
			</CButton>
		</div>
	</CCard>
</template>

<script>
import {CButton, CCard} from '@coreui/vue/src';
import DaemonModeService from '../../services/DaemonModeService';

export default {
	name: 'DaemonMode',
	components: {
		CButton,
		CCard,
	},
	data() {
		return {
			loaded: false,
			mode: 'unknown',
		};
	},
	created() {
		this.unsubscribe = this.$store.subscribe(mutation => {
			if (mutation.type === 'SOCKET_ONOPEN') {
				this.getMode();
				return;
			}
			if (mutation.type !== 'SOCKET_ONMESSAGE' ||
					mutation.payload.mType !== 'mngDaemon_Mode') {
				return;
			}
			try {
				this.mode = mutation.payload.data.rsp.operMode;
				this.$store.commit('spinner/HIDE');
				if (this.loaded) {
					this.$toast.success(
						this.$t('gateway.mode.messages.' + this.mode).toString()
					);
				} else {
					this.loaded = true;
				}
			} catch (e) {
				this.$store.commit('spinner/HIDE');
				this.mode = 'unknown';
				this.$toast.error(
					this.$t('gateway.mode.messages.' + this.loaded ? 'set' : 'get')
						.toString()
				);
			}
		});
		if (this.$store.state.webSocketClient.socket.isConnected) {
			this.getMode();
		}
	},
	beforeDestroy() {
		this.unsubscribe();
	},
	methods: {
		getMode() {
			this.$store.dispatch('spinner/show', {timeout: 10000});
			DaemonModeService.get();
		},
		setMode(newMode) {
			this.$store.dispatch('spinner/hide');
			DaemonModeService.set(newMode);
		},
	},
	metaInfo: {
		title: 'gateway.mode.title',
	},
};
</script>
