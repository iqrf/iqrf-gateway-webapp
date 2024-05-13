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
