<template>
	<IModalWindow
		v-model='show'
		persistent
	>
		<ICard header-color='warning'>
			<template #title>
				{{ $t('components.iqrfnet.upload.dpa-plugin.modal.title') }}
			</template>
			{{ $t('components.iqrfnet.upload.dpa-plugin.modal.text', { version: current }) }}
			<template #actions>
				<IActionBtn
					:action='Action.Upload'
					color='warning'
					@click='upload()'
				/>
				<v-spacer />
				<IActionBtn
					:action='Action.Cancel'
					@click='close()'
				/>
			</template>
		</ICard>
	</IModalWindow>
</template>

<script lang='ts' setup>

import { Action, IActionBtn, ICard, IModalWindow } from '@iqrf/iqrf-vue-ui';
import { ref, Ref } from 'vue';

defineProps({
	current: {
		type: String,
		required: true,
	},
});
defineExpose({
	open,
});
const emit = defineEmits<{
	upload: [];
}>();
const show: Ref<boolean> = ref(false);

function open(): void {
	show.value = true;
}

function upload(): void {
	emit('upload');
	close();
}

function close(): void {
	show.value = false;
}

</script>
