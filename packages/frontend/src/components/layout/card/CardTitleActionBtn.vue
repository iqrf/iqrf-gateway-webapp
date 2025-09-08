<!--
Copyright 2017-2025 IQRF Tech s.r.o.
Copyright 2019-2025 MICRORISC s.r.o.

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
	<v-btn
		v-tooltip:bottom='tooltip'
		v-bind='props'
		:color='props.color'
		:icon='icon'
		variant='flat'
		@click='emit("click", $event)'
	/>
</template>

<script lang='ts' setup>
import { mdiHelp } from '@mdi/js';
import { computed, type PropType, type Ref } from 'vue';

import { ActionUtils } from '@/helpers/ActionUtils';
import { Action } from '@/types/Action';

const emit = defineEmits(['click']);
const props = defineProps({
	action: {
		type: String as PropType<Action>,
		required: false,
		default: Action.Custom,
	},
	color: {
		type: String,
		default: 'primary',
		required: false,
	},
	icon: {
		type: [String, null] as PropType<string | null>,
		required: false,
		default: null,
	},
	tooltip: {
		type: [String, null] as PropType<string | null>,
		required: false,
		default: null,
	},
});

/// Icon for the action button
const icon: Ref<string> = computed((): string => {
	if (props.action === Action.Custom) {
		return props.icon ?? mdiHelp;
	}
	return ActionUtils.getIcon(props.action);
});

/// Tooltip text
const tooltip: Ref<string> = computed((): string => {
	if (props.tooltip !== null) {
		return props.tooltip;
	}
	return ActionUtils.getText(props.action);
});
</script>
