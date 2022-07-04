<template>
	<div>
		<h1>{{ $t('maintenance.mender.update.pageTitle') }}</h1>
		<v-row>
			<v-col md='6'>
				<MenderUpdateControl @update-log='updateLog' />
			</v-col>
			<v-col md='6'>
				<MenderUpdateLog :log='log' />
			</v-col>
		</v-row>
	</div>
</template>

<script lang='ts'>
import {Component, Vue} from 'vue-property-decorator';
import MenderUpdateControl from '@/components/Maintenance/MenderUpdateControl.vue';
import MenderUpdateLog from '@/components/Maintenance/MenderUpdateLog.vue';

import {NavigationGuardNext, Route} from 'vue-router';

@Component({
	components: {
		MenderUpdateControl,
		MenderUpdateLog,
	},
	beforeRouteEnter(_to: Route, from: Route, next: NavigationGuardNext): void {
		next((vm: Vue) => {
			if (!vm.$store.getters['features/isEnabled']('mender')) {
				vm.$toast.error(vm.$t('service.mender-client.messages.disabled').toString());
				vm.$router.push(from.path);
			}
		});
	},
	metaInfo: {
		title: 'maintenance.mender.update.pageTitle',
	},
})

/**
 * Mender update page component
 */
export default class MenderUpdate extends Vue {

	/**
	 * @var {string} log Execution log
	 */
	private log = '';

	/**
	 * Updates mender execution output log
	 * @param {string} log Output
	 */
	private updateLog(log: string) {
		if (this.log.length === 0) {
			this.log += log;
		} else {
			this.log += '\n' + log;
		}
	}

}
</script>
