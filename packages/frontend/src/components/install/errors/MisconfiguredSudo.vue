<template>
	<v-stepper-vertical-item
		:value='componentProps.index'
		:title='$t("components.install.errors.misconfiguredSudo.title")'
	>
		<div v-if='!model.exists'>
			{{ $t('components.install.errors.misconfiguredSudo.texts.missing') }}
			<v-divider class='my-2' />
			{{ $t('components.install.errors.misconfiguredSudo.fixes.missing') }}
			<br>
			<pre>{{ missingCommand }}</pre>
		</div>
		<div v-if='!model.userSudo'>
			{{ $t('components.install.errors.misconfiguredSudo.texts.invalid') }}
			<v-divider class='my-2' />
			{{ $t('components.install.errors.misconfiguredSudo.fixes.invalid', { user: model.user }) }}
			<br>
			<pre>{{ invalidCommand }}</pre>
		</div>
		<template #actions='{ prev, next }'>
			<ErrorStepperActions :index='componentProps.index' :prev='prev' :next='next' />
		</template>
	</v-stepper-vertical-item>
</template>

<script setup lang='ts'>
import { InstallationCheckSudo } from '@iqrf/iqrf-gateway-webapp-client/types';
import { computed, type ComputedRef } from 'vue';

import ErrorStepperActions
	from '@/components/install/errors/ErrorStepperActions.vue';

const model = defineModel<InstallationCheckSudo>({
	required: true,
});
const componentProps = defineProps<{
	index: number;
}>();
const invalidCommand: ComputedRef<string> = computed((): string => `echo "${model.value.user} ALL=(ALL) NOPASSWD:ALL" | sudo tee -a /etc/sudoers.d/iqaros-webapp >/dev/null`);
const missingCommand: string = 'su\napt-get install sudo';
</script>
