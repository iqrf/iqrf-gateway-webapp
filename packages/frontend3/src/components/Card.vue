<template>
	<v-card :class='bottomMargin ? "mb-4" : null'>
		<v-card-title v-if='$slots.title' :class='headerClass'>
			<slot name='title' />
		</v-card-title>
		<v-card-text class='card-text'>
			<slot />
		</v-card-text>
		<v-card-actions v-if='$slots.actions' :class='actionsClass'>
			<slot name='actions' />
		</v-card-actions>
	</v-card>
</template>

<script lang='ts' setup>
import {computed} from 'vue';
const props = defineProps({
	headerColor: {
		type: String,
		default: 'primary',
		required: false
	},
	actionsColor: {
		type: String,
		default: 'grey-lighten-2',
		required: false,
	},
	bottomMargin: {
		type: Boolean,
		default: false,
		required: false,
	},
});
const actionsClass = computed(() => `bg-${props.actionsColor}`);
const headerClass = computed(() => `bg-${props.headerColor}`);
</script>

<style>
.card-text {
	padding: 1rem !important;
}
</style>
