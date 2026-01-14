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
		<template #actions='{ prev, next }'>
			<ErrorStepperActions :index='componentProps.index' :prev='prev' :next='next' />
		</template>
	</v-stepper-vertical-item>
</template>

<script setup lang='ts'>
import {
	type InstallationCheckPhpMissingExtensions,
} from '@iqrf/iqrf-gateway-webapp-client/types';
import { computed, type ComputedRef } from 'vue';

import ErrorStepperActions
	from '@/components/install/errors/ErrorStepperActions.vue';

const model = defineModel<InstallationCheckPhpMissingExtensions | null>({
	required: true,
});
const componentProps = defineProps<{
	index: number;
}>();
const extensions: ComputedRef<string> = computed((): string => {
	return model.value?.extensions.join(', ') ?? '';
});
const command: ComputedRef<string> = computed((): string => {
	return `sudo apt-get update\nsudo apt-get install ${model.value?.packages?.join(' ')}`;
});
</script>
