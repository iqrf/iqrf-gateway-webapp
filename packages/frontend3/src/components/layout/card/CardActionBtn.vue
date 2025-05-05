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
		:color='color'
		:disabled='disabled'
		:prepend-icon='icon'
		variant='elevated'
		:text='text'
		@click='emit("click", $event)'
	/>
</template>

<script lang='ts' setup>
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
	disabled: {
		type: Boolean,
		required: false,
		default: false,
	},
	icon: {
		type: [String, undefined] as PropType<string | undefined>,
		required: false,
		default: undefined,
	},
	text: {
		type: [String, null] as PropType<string | null>,
		required: false,
		default: null,
	},
});

/// Color of the action button
const color: Ref<string> = computed((): string => {
	if (props.color !== null) {
		return props.color;
	}
	return ActionUtils.getColor(props.action);
});

/// Icon for the action button
const icon: Ref<string|undefined> = computed((): string|undefined => {
	if (props.action === Action.Custom) {
		return props.icon;
	}
	return ActionUtils.getIcon(props.action);
});

/// Button text
const text: Ref<string> = computed((): string => {
	if (props.text !== null) {
		return props.text;
	}
	return ActionUtils.getText(props.action);
});
</script>
