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

<script lang='ts'>
import Vue from 'vue';
import {CButton, CCard} from '@coreui/vue/src';
import DaemonModeService, {DaemonMode} from '../../services/DaemonModeService';

export default Vue.extend({
	name: 'DaemonMode',
	components: {
		CButton,
		CCard,
	},
	data() {
		return {
			loaded: false,
			mode: DaemonMode.unknown,
			unsubscribe: () => {},
		};
	},
	created() {
		if (this.$store.state.webSocketClient.socket.isConnected) {
			this.getMode();
		}
		this.unsubscribe = this.$store.subscribe((mutation: any) => {
			if (mutation.type === 'SOCKET_ONOPEN' &&
					this.mode === DaemonMode.unknown) {
				this.getMode();
				return;
			}
			if (mutation.type !== 'SOCKET_ONMESSAGE' ||
					mutation.payload.mType !== 'mngDaemon_Mode') {
				return;
			}
			this.handleResponse(mutation.payload);
		});
	},
	beforeDestroy() {
		this.unsubscribe();
	},
	methods: {
		handleResponse(response: any) {
			this.mode = DaemonModeService.parse(response);
			this.$store.commit('spinner/HIDE');
			if (this.mode === DaemonMode.unknown) {
				this.$toast.error(
					this.$t('gateway.mode.messages.' + this.loaded ? 'set' : 'get')
						.toString()
				);
			} else if (this.loaded) {
				this.$toast.success(
					this.$t('gateway.mode.messages.' + this.mode).toString()
				);
			} else {
				this.loaded = true;
			}
		},
		getMode() {
			this.$store.dispatch('spinner/show', {timeout: 10000});
			DaemonModeService.get();
		},
		setMode(newMode: DaemonMode|string) {
			this.$store.dispatch('spinner/hide');
			DaemonModeService.set(newMode as DaemonMode);
		},
	},
	metaInfo: {
		title: 'gateway.mode.title',
	},
});
</script>
