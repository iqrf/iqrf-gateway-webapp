<template>
	<v-chip
		:color='stateColor'
		:prepend-icon='stateIcon'
	>
		{{ $t(`components.accessControl.users.states.${state}`) }}
	</v-chip>
</template>

<script lang='ts' setup>
import { computed } from 'vue';
import { AccountState } from '@iqrf/iqrf-gateway-webapp-client/types/User';
import { mdiCheck, mdiHelp } from '@mdi/js';

interface Props {
	state: AccountState
}

const props = defineProps<Props>();

const stateColor = computed(() => {
	if (props.state == AccountState.Unverified) {
		return 'orange';
	}
	return 'green';
});

const stateIcon = computed(() => {
	if (props.state == AccountState.Unverified) {
		return mdiHelp;
	}
	return mdiCheck;
});

</script>
