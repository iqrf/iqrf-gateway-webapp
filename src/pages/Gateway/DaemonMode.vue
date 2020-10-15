<template>
	<div>
		<h1>{{ $t('gateway.mode.title') }}</h1>
		<CCard body-wrapper>
			<table class='table table-striped'>
				<tr>
					<th>{{ $t('gateway.info.gwMode') }}</th>
					<td>{{ $t('gateway.mode.modes.' + mode) }}</td>
				</tr>
			</table>
			<div v-if='mode !== "unknown"'>
				<CButton
					color='primary'
					@click='setMode("operational")'
				>
					{{ $t('gateway.mode.modes.operational') }}
				</CButton> <CButton
					color='primary'
					@click='setMode("service")'
				>
					{{ $t('gateway.mode.modes.service') }}
				</CButton> <CButton
					color='primary'
					@click='setMode("forwarding")'
				>
					{{ $t('gateway.mode.modes.forwarding') }}
				</CButton>
			</div>
		</CCard>
	</div>
</template>

<script lang='ts'>
import Vue from 'vue';
import {MutationPayload} from 'vuex';
import {CButton, CCard} from '@coreui/vue/src';
import DaemonModeService, {DaemonMode} from '../../services/DaemonModeService';

export default Vue.extend({
	name: 'DaemonMode',
	components: {
		CButton,
		CCard,
	},
	data(): any {
		return {
			loaded: false,
			mode: DaemonMode.unknown,
			allowedMTypes: [
				'mngDaemon_Mode',
				'messageError'
			],
			msgId: null,
		};
	},
	created() {
		this.unsubscribe = this.$store.subscribe((mutation: MutationPayload) => {
			if (mutation.type === 'SOCKET_ONMESSAGE') {
				if (!this.allowedMTypes.includes(mutation.payload.mType)) {
					return;
				}
				if (mutation.payload.data.msgId === this.msgId) {
					this.$store.dispatch('removeMessage', this.msgId);
					this.handleResponse(mutation.payload);
				}
			}
		});
		if (this.$store.getters.isSocketConnected) {
			this.getMode();
		} else {
			this.unwatch = this.$store.watch(
				(state: any, getter: any) => getter.isSocketConnected,
				(newVal: boolean, oldVal: boolean) => {
					if (!oldVal && newVal) {
						this.getMode();
						this.unwatch();
					}
				}
			);
		}
	},
	beforeDestroy() {
		this.$store.dispatch('removeMessage', this.msgId);
		if (this.unwatch !== undefined) {
			this.unwatch();
		}
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
			DaemonModeService.get(5000, 'gateway.mode.messages.failures.get', () => this.msgId = null)
				.then((msgId: string) => this.msgId = msgId);
		},
		setMode(newMode: DaemonMode|string) {
			this.$store.dispatch('spinner/hide');
			DaemonModeService.set(newMode as DaemonMode, 5000, 'gateway.mode.messages.failures.set', () => this.msgId = null)
				.then((msgId: string) => this.msgId = msgId);
		},
	},
	metaInfo: {
		title: 'gateway.mode.title',
	},
});
</script>
