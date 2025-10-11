<template>
	<IModalWindow
		v-model='show'
		persistent
	>
		<ICard>
			<template #title>
				{{ $t('components.iqrfnet.upload.dpa-plugin.dialog.title') }}
			</template>
			<div class='text-center'>
				{{ message }}
				<v-divider class='my-2' />
				<v-progress-linear
					:indeterminate='componentState === ComponentState.Action'
					model-value='100'
					:color='progressColor'
					rounded
					height='24'
				/>
			</div>
			<template #actions>
				<IActionBtn
					:action='Action.Close'
					:disabled='componentState === ComponentState.Action'
					@click='close()'
				/>
			</template>
		</ICard>
	</IModalWindow>
</template>

<script lang='ts' setup>
import { Action, ComponentState, IActionBtn, ICard, IModalWindow } from '@iqrf/iqrf-vue-ui';
import { computed, type PropType, ref, type Ref } from 'vue';

defineExpose({
	open,
});
const componentProps = defineProps({
	componentState: {
		type: String as PropType<ComponentState>,
		required: true,
	},
	message: {
		type: String,
		required: false,
		default: '',
	},
});
const show: Ref<boolean> = ref(false);
const progressColor = computed(() => {
	if (componentProps.componentState === ComponentState.Action || componentProps.componentState === ComponentState.Success) {
		return 'green';
	}
	return 'red';
});

function open(): void {
	show.value = true;
}

function close(): void {
	show.value = false;
}
</script>
