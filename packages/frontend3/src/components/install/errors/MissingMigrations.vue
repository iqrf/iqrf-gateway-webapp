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
	</v-stepper-vertical-item>
</template>

<script setup lang='ts'>
import { ref, Ref } from 'vue';

const componentProps = defineProps({
	index: {
		type: Number,
		required: true,
	},
});
const command: Ref<string> = ref('sudo iqrf-gateway-webapp-manager migrations:migrate --no-interaction');
</script>
