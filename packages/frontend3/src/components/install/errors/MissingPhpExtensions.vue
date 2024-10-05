<template>
	<v-stepper-vertical-item
		v-if='model !== null'
		:value='componentProps.index'
		:title='$t("components.install.errors.missingPhpExtensions.title")'
	>
		{{ $t('components.install.errors.missingPhpExtensions.text', { extensions: extensions }) }}
		<v-divider class='my-2' />
		{{ $t('components.install.errors.missingPhpExtensions.fix') }}
		<br>
		<pre>{{ command }}</pre>
	</v-stepper-vertical-item>
</template>

<script setup lang='ts'>
import {
	type InstallationCheckPhpMissingExtensions,
} from '@iqrf/iqrf-gateway-webapp-client/types';
import { computed, ComputedRef, PropType } from 'vue';

const model = defineModel({
	required: true,
	type: [Object, null] as PropType<InstallationCheckPhpMissingExtensions | null>,
});
const componentProps = defineProps({
	index: {
		type: Number,
		required: true,
	},
});
const extensions: ComputedRef<string> = computed((): string => {
	return model.value?.extensions.join(', ') ?? '';
});
const command: ComputedRef<string> = computed((): string => {
	return `sudo apt-get update\nsudo apt-get install ${ model.value?.packages?.join(' ')}`;
});
</script>
