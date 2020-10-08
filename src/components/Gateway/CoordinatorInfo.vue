<template>
	<span v-if='requestRunning'>
		<CSpinner color='info' class='cinfo-spinner' />
	</span>
	<span v-else>
		<span v-if='hasData'>
			<strong>{{ $t('gateway.info.tr.moduleType') }}: </strong>
			{{ trMcuType.trType }}<br>
			<strong>{{ $t('gateway.info.tr.mcuType') }}: </strong>
			{{ trMcuType.mcuType }}<br>
			<strong>{{ $t('gateway.info.tr.moduleId') }}: </strong> {{ osInfo.mid }}<br>
			<strong>{{ $t('gateway.info.tr.os') }}: </strong>
			{{ osInfo.osVersion }} ({{ osInfo.osBuild }})<br>
			<strong>{{ $t('gateway.info.tr.dpa') }}: </strong>
			{{ enumeration.dpaVer }}<br>
			<span v-if='enumeration.flags.rfMode'>
				<strong>{{ $t('gateway.info.tr.rfMode') }}</strong>
				{{ enumeration.flags.rfMode }}
			</span>
			<span v-else-if='enumeration.flags.networkType'>
				<strong>{{ $t('gateway.info.tr.networkType') }}</strong>
				{{ enumeration.flags.networkType }}
			</span>
			<strong>{{ $t('gateway.info.tr.voltage') }}: </strong>
			{{ osInfo.supplyVoltage }}<br>
		</span>
		<span v-else>
			{{ $t('gateway.info.tr.error') }}
		</span>
	</span>
</template>

<script lang='ts'>
import Vue from 'vue';
import {Getter, MutationPayload} from 'vuex';
import IqrfNetService from '../../services/IqrfNetService';
import {CSpinner} from '@coreui/vue/src';

export default Vue.extend({
	name: 'CoordinatorInfo',
	components: {
		CSpinner,
	},
	data(): any {
		return {
			enumeration: null,
			hasData: false,
			osInfo: null,
			trMcuType: null,
			requestRunning: false,
			allowedMTypes: [
				'iqmeshNetwork_EnumerateDevice',
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
				this.requestRunning = false;
				if (mutation.payload.data.msgId === this.msgId) {
					this.$store.dispatch('removeMessage', this.msgId);
					try {
						const data = mutation.payload.data.rsp;
						this.enumeration = data.peripheralEnumeration;
						this.osInfo = data.osRead;
						this.trMcuType = this.osInfo.trMcuType;
						this.hasData = true;
					} catch (e) {
						this.hasData = false;
					}
				}
			}
			
		});
		if (this.$store.getters.isSocketConnected) {
			this.enumerate();
		} else {
			this.unwatch = this.$store.watch(
				(state: any, getter: any) => getter.isSocketConnected,
				(newVal: boolean, oldVal: boolean) => {
					if (!oldVal && newVal) {
						this.enumerate();
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
		enumerate() {
			IqrfNetService.enumerateDevice(0, 5000, 'iqrfnet.enumeration.messages.failure', () => this.timedOut())
				.then((msgId: string) => this.msgId = msgId);
			this.requestRunning = true;
		},
		timedOut() {
			this.requestRunning = false;
			this.msgId = null;
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
