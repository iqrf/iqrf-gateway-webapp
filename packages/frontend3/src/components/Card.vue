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
	<v-card :class='bottomMargin ? "mb-4" : null'>
		<v-toolbar
			v-if='$slots.title || $slots.actions'
			:class='headerClass'
			density='compact'
		>
			<v-toolbar-title v-if='$slots.title'>
				<slot name='title' />
			</v-toolbar-title>
			<v-toolbar-items v-if='$slots.titleActions'>
				<slot name='titleActions' />
			</v-toolbar-items>
			<template v-if='$slots.extension' #extension>
				<slot name='extension' />
			</template>
		</v-toolbar>
		<v-card-text class='card-text'>
			<slot />
		</v-card-text>
		<v-card-actions v-if='$slots.actions' :class='actionsClass'>
			<slot name='actions' />
		</v-card-actions>
	</v-card>
</template>

<script lang='ts' setup>
import { computed } from 'vue';
import { useTheme } from 'vuetify';

const theme = useTheme();
const props = defineProps({
	headerColor: {
		type: String,
		default: 'primary',
		required: false,
	},
	actionsColor: {
		type: String,
		default: 'default',
		required: false,
	},
	bottomMargin: {
		type: Boolean,
		default: false,
		required: false,
	},
});
const actionsClass = computed(() => {
	let actionsColor = props.actionsColor;
	const isLight = theme.global.name.value === 'light';
	if (actionsColor === 'default') {
		actionsColor = isLight ? 'grey-lighten-2' : 'grey-darken-3';
	}
	return `bg-${actionsColor}`;
});
const headerClass = computed(() => `bg-${props.headerColor}`);
</script>

<style>
.card-text {
	padding: 1rem !important;
}
</style>
