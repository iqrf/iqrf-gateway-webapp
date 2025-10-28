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
				{{ $t('components.accessControl.apiKeys.display.title') }}
			</template>
			{{ $t('components.accessControl.apiKeys.display.prompt') }}
			<ITextInput
				class='mt-4'
				:model-value='apiKey'
				readonly
				variant='solo-filled'
				density='compact'
			>
				<template #append>
					<v-btn
						color='success'
						@click='copyToClipboard()'
					>
						<v-icon :icon='mdiClipboard' />
						{{ $t('common.buttons.clipboard') }}
					</v-btn>
				</template>
			</ITextInput>
			<template #actions>
				<IActionBtn
					:action='Action.Close'
					container-type='card'
					@click='close()'
				/>
			</template>
		</ICard>
	</IModalWindow>
</template>

<script lang='ts' setup>
import {
	Action,
	IActionBtn,
	ICard,
	IModalWindow,
	ITextInput,
} from '@iqrf/iqrf-vue-ui';
import { mdiClipboard } from '@mdi/js';
import { type PropType, ref, type Ref } from 'vue';
import { useI18n } from 'vue-i18n';
import { toast } from 'vue3-toastify';

const componentProps = defineProps({
	apiKey: {
		type: [String, null] as PropType<string | null>,
		default: null,
		required: false,
	},
});
const emit = defineEmits<{
	closed: [];
}>();
const show: Ref<boolean> = ref(false);
const i18n = useI18n();

function copyToClipboard(): void {
	navigator.clipboard.writeText(componentProps.apiKey!);
	toast.success(
		i18n.t('components.accessControl.apiKeys.display.copyMessage'),
	);
}

function open(): void {
	show.value = true;
}

function close(): void {
	show.value = false;
	emit('closed');
}

defineExpose({
	open,
});
</script>
