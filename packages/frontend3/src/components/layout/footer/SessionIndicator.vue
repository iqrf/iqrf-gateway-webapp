<template>
	<v-tooltip location='bottom'>
		<template #activator='{ props }'>
			<v-icon
				v-bind='props'
				:color='color'
				size='small'
			>
				{{ mdiTimerSand }}
			</v-icon>
		</template>
		{{ $t('components.status.sessionExpiration.indicator') }} {{ remaining }}
	</v-tooltip>
</template>

<script lang='ts' setup>
import { mdiTimerSand } from '@mdi/js';
import { storeToRefs } from 'pinia';
import { computed, onMounted, ref, type Ref , onBeforeUnmount } from 'vue';

import TimeConverter from '@/helpers/TimeConverter';
import { useUserStore } from '@/store/user';


const userStore = useUserStore();
const { getExpiration: expiration } = storeToRefs(userStore);
const now: Ref<number> = ref(0);
let interval = 0;

onMounted(() => {
	interval = window.setInterval(() => {
		now.value = Date.now();
	}, 1000);
});

onBeforeUnmount(() => {
	window.clearInterval(interval);
});

const remaining = computed((): string => {
	const minutes = Math.ceil(expiration.value - (now.value / 1000));
	return TimeConverter.secondsToDuration(minutes);
});

const color = computed(() => {
	if (expiration.value - (now.value / 1000) > 5) {
		return 'light-green-accent-3';
	}
	return 'warning';
});

</script>
