<!--
Copyright 2017-2024 IQRF Tech s.r.o.
Copyright 2019-2024 MICRORISC s.r.o.

Licensed under the Apache License, Version 2.0 (the "License");
you may not use this file except in compliance with the License.
You may obtain a copy of the License at

    http://www.apache.org/licenses/LICENSE-2.0

Unless required by applicable law or agreed to in writing, software,
distributed under the License is distributed on an "AS IS" BASIS,
WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied
See the License for the specific language governing permissions and
limitations under the License.
-->

<template>
	<v-chip
		:color='stateColor'
		:prepend-icon='stateIcon'
	>
		{{ $t(`components.accessControl.users.states.${state}`) }}
	</v-chip>
</template>

<script lang='ts' setup>
import { AccountState } from '@iqrf/iqrf-gateway-webapp-client/types';
import { mdiCheck, mdiHelp } from '@mdi/js';
import { computed } from 'vue';

interface Props {
	state: AccountState
}

const props = defineProps<Props>();

const stateColor = computed(() => {
	if (props.state === AccountState.Unverified) {
		return 'orange';
	}
	return 'green';
});

const stateIcon = computed(() => {
	if (props.state === AccountState.Unverified) {
		return mdiHelp;
	}
	return mdiCheck;
});

</script>
