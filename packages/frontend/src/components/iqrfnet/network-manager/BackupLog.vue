<!--
Copyright 2017-2025 IQRF Tech s.r.o.
Copyright 2019-2025 MICRORISC s.r.o.

Licensed under the Apache License, Version 2.0 (the "License");
you may not use this file except in compliance with the License.
You may obtain a copy of the License at

    http://www.apache.org/licenses/LICENSE-2.0

Unless required by applicable law or agreed to in writing, software,
distributed under the License is distributed on an "AS IS" BASIS,
WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied
See the License for the specific language governing permissions and
limitations under the License.
-->

<template>
	<IModalWindow
		v-model='show'
		persistent
	>
		<ICard>
			<template #title>
				{{ $t('components.iqrfnet.network-manager.backup.log') }}
			</template>
			<v-progress-linear
				:model-value='progress'
				color='info'
				height='24'
				rounded
			/>
			<v-divider class='my-2' />
			<pre class='log'>{{ messages.join('\n') }}</pre>
			<template #actions>
				<IActionBtn
					:action='Action.Download'
					:text='$t("components.iqrfnet.network-manager.backup.actions.download")'
					:disabled='componentState === ComponentState.Action || dataLen === 0'
					@click='emit("generateBackup")'
				/>
				<v-spacer />
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
import { type PropType, ref, Ref } from 'vue';

defineProps({
	messages: {
		type: Array as PropType<string[]>,
		required: true,
	},
	progress: {
		type: Number,
		required: true,
	},
	componentState: {
		type: String as PropType<ComponentState>,
		required: true,
	},
	dataLen: {
		type: Number,
		required: true,
	},
});
const emit = defineEmits<{
  generateBackup: []
}>();
const show: Ref<boolean> = ref(false);

const open = () => show.value = true;

const close = () => show.value = false;

defineExpose({
	open,
});
</script>
