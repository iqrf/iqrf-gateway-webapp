<template>
	<v-stepper-vertical-item
		:value='componentProps.index'
		:title='$t("components.install.errors.missingMigrations.title")'
	>
		{{ $t('components.install.errors.missingMigrations.text') }}
		<v-divider class='my-2' />
		{{ $t('components.install.errors.missingMigrations.fix') }}
		<br>
		<pre>{{ command }}</pre>
		<template #actions='{ prev, next }'>
			<ErrorStepperActions :index='componentProps.index' :prev='prev' :next='next' />
		</template>
	</v-stepper-vertical-item>
</template>

<script setup lang='ts'>
import { ref, Ref } from 'vue';

import ErrorStepperActions
	from '@/components/install/errors/ErrorStepperActions.vue';

const componentProps = defineProps<{
	index: number;
}>();
const command: Ref<string> = ref('sudo iqrf-gateway-webapp-manager migrations:migrate --no-interaction');
</script>
