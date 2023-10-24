<template>
	<v-tooltip location='bottom'>
		<template #activator='{ props }'>
			<v-icon
				class='me-2'
				color='info'
				size='small'
				v-bind='props'
			>
				{{ mdiInformation }}
			</v-icon>
		</template>
		{{ $t('components.status.monitor.version') }}: {{ daemonVersion }}
	</v-tooltip>
	<v-tooltip location='bottom'>
		<template #activator='{ props }'>
			<v-icon
				class='me-2'
				:color='connectionColor'
				size='small'
				v-bind='props'
			>
				{{ mdiConnection }}
			</v-icon>
		</template>
		{{ $t('components.status.monitor.notified') }}: {{ date }}
	</v-tooltip>
	<v-tooltip location='bottom'>
		<template #activator='{ props }'>
			<v-icon
				class='me-2'
				:color='modeIconColor'
				size='small'
				v-bind='props'
			>
				{{ modeIcon }}
			</v-icon>
		</template>
		{{ $t('components.status.monitor.mode') }}:
		{{ $t(`components.status.monitor.modes.${monitorStore.mode}`) }}
	</v-tooltip>
	<v-tooltip location='bottom'>
		<template #activator='{ props }'>
			<v-icon
				class='me-2'
				:color='queueIconColor'
				size='small'
				v-bind='props'
			>
				{{ queueIcon }}
			</v-icon>
		</template>
		{{ $t('components.status.monitor.queue') }}: {{ daemonStore.isConnected ? monitorStore.queueLength : 'N/A' }}
	</v-tooltip>
	<EnumerationIndicator />
	<SessionIndicator v-if='loggedIn' />
</template>

<script lang="ts" setup>
import { computed } from 'vue';
import { DateTime } from 'luxon';
import { useMonitorStore } from '@/store/monitorSocket';
import { DaemonMode } from '@iqrf/iqrf-gateway-daemon-utils/enums';
import { useDaemonStore } from '@/store/daemonSocket';
import { useUserStore } from '@/store/user';
import { storeToRefs } from 'pinia';
import EnumerationIndicator from '@/components/layout/footer/EnumerationIndicator.vue';
import SessionIndicator from '@/components/layout/footer/SessionIndicator.vue';
import { mdiCheckCircleOutline, mdiConnection, mdiForward, mdiHelp, mdiInformation, mdiTray, mdiTrayAlert, mdiTrayFull, mdiTrayRemove, mdiWrench } from '@mdi/js';

const daemonStore = useDaemonStore();
const { getVersion: version } = storeToRefs(daemonStore);
const monitorStore = useMonitorStore();
const userStore = useUserStore();
const { isLoggedIn: loggedIn } = storeToRefs(userStore);

const daemonVersion = computed(() => {
	if (version.value.length > 0) {
		return version.value;
	}
	return 'N/A';
});

const connectionColor = computed(() => {
	if (monitorStore.connected) {
		return 'light-green-accent-3';
	} else {
		return 'red-accent-3';
	}
});

const date = computed(() => {
	return DateTime.fromSeconds(monitorStore.lastTimestamp).toLocaleString(
		DateTime.DATETIME_FULL_WITH_SECONDS
	);
});

const modeIcon = computed(() => {
	const mode: DaemonMode = monitorStore.mode;
	switch (mode) {
		case DaemonMode.Operational:
			return mdiCheckCircleOutline;
		case DaemonMode.Forwarding:
			return mdiForward;
		case DaemonMode.Service:
			return mdiWrench;
		default:
			return mdiHelp;
	}
});

const modeIconColor = computed(() => {
	const mode: DaemonMode = monitorStore.mode;
	switch (mode) {
		case DaemonMode.Operational:
		case DaemonMode.Forwarding:
			return 'light-green-accent-3';
		case DaemonMode.Service:
			return 'warning';
		default:
			return 'red-accent-3';
	}
});

const queueIcon = computed(() => {
	if (!daemonStore.isConnected) {
		return mdiTrayRemove;
	}
	const len = monitorStore.queueLength;
	if (len <= 16) {
		return mdiTray;
	} else if (len <= 24) {
		return mdiTrayFull;
	} else {
		return mdiTrayAlert;
	}
});

const queueIconColor = computed(() => {
	if (!daemonStore.isConnected) {
		return 'red-accent-3';
	}
	const len = monitorStore.queueLength;
	if (len <= 16) {
		return 'light-green-accent-3';
	} else if (len <= 24) {
		return 'warning';
	} else {
		return 'red-accent-3';
	}
});
</script>
