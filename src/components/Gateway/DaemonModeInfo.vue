<template>
	<span v-if='requestRunning && mode === "unknown"'>
		<CSpinner color='info' class='cinfo-spinner' />
	</span>
	<span v-else>{{ $t('gateway.mode.modes.' + mode) }}</span>
</template>

<script lang='ts'>
import Vue from 'vue';
import {Getter, MutationPayload} from 'vuex';
import DaemonModeService, {DaemonMode} from '../../services/DaemonModeService';

export default Vue.extend({
	name: 'DaemonModeInfo',
	data(): any {
		return {
			mode: DaemonMode.unknown,
			allowedMTypes: [
				'mngDaemon_Mode',
				'messageError'
			],
			msgId: null,
			requestRunning: false,
		};
	},
	created() {
		this.unsubscribe = this.$store.subscribe((mutation: MutationPayload) => {
			if (mutation.type === 'SOCKET_ONMESSAGE') {
				if (!this.allowedMTypes.includes(mutation.payload.mType)) {
					return;
				}
				this.requestRunning = false;
				if (mutation.payload.data.msgId === this.msgId) {
					this.$store.dispatch('removeMessage', this.msgId);
					this.mode = DaemonModeService.parse(mutation.payload);
					this.$emit('notify-cinfo');
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
		getMode() {
			DaemonModeService.get(5000, 'gateway.mode.modes.unknown', () => this.timedOut())
				.then((msgId: string) => this.msgId = msgId);
			this.requestRunning = true;
		},
		timedOut() {
			this.requestRunning = false;
			this.msgId = null;
			this.$emit('notify-cinfo');
		}
	}
});
</script>

<style scoped>
.cinfo-spinner {
	width: 2rem;
	height: 2rem;
}
</style>
