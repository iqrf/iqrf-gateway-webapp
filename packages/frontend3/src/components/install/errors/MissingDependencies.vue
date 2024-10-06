<template>
	<v-stepper-vertical-item
		:value='componentProps.index'
		:title='$t("components.install.errors.missingDependencies.title")'
	>
		{{ $t('components.install.errors.missingDependencies.text', { dependencies: packages }) }}
		<v-table :hover='true'>
			<thead>
				<tr>
					<th>{{ $t('components.install.errors.missingDependencies.command') }}</th>
					<th>{{ $t('components.install.errors.missingDependencies.package') }}</th>
				</tr>
			</thead>
			<tbody>
				<tr
					v-for='(item, i) in model'
					:key='i'
				>
					<td>{{ item.command }}</td>
					<td>{{ item.package }}</td>
				</tr>
			</tbody>
		</v-table>
		<template #actions='{ prev, next }'>
			<ErrorStepperActions :index='componentProps.index' :prev='prev' :next='next' />
		</template>
	</v-stepper-vertical-item>
</template>

<script setup lang='ts'>
import { InstallationCheckDependency } from '@iqrf/iqrf-gateway-webapp-client/types';
import { computed, type ComputedRef, PropType } from 'vue';

import ErrorStepperActions
	from '@/components/install/errors/ErrorStepperActions.vue';

const model = defineModel({
	required: true,
	type: Array as PropType<InstallationCheckDependency[]>,
});
const componentProps = defineProps({
	index: {
		type: Number,
		required: true,
	},
});
const packages: ComputedRef<string> = computed((): string => {
	const dependencies = model.value.map((item: InstallationCheckDependency): string => item.package);
	return dependencies.filter((value: string, index: number, array: string[]) => array.indexOf(value) === index).join(', ');
});
</script>
