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
	<v-icon
		v-tooltip:bottom='tooltip'
		v-bind='props'
		:color='color'
		:class='classes'
		:icon='icon'
		size='large'
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
		type: [String, null] as PropType<string | null>,
		required: false,
		default: null,
	},
	icon: {
		type: [String, null] as PropType<string | null>,
		required: false,
		default: null,
	},
	last: {
		type: Boolean,
		required: false,
		default: false,
	},
	tooltip: {
		type: [String, null] as PropType<string | null>,
		required: false,
		default: null,
	},
});

/// CSS classes
const classes: Ref<string> = computed((): string => {
	const classes: string[] = [];
	if (!props.last) {
		classes.push('me-2');
	}
	return classes.join(' ');
});

/// Color of the action button
const color: Ref<string> = computed((): string => {
	if (props.color !== null) {
		return props.color;
	}
	return ActionUtils.getColor(props.action);
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
