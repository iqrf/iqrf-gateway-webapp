<!--
Copyright 2017-2026 IQRF Tech s.r.o.
Copyright 2019-2026 MICRORISC s.r.o.

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
				{{ $t('components.accessControl.daemonAccessTokens.display.title') }}
			</template>
			{{ $t('components.accessControl.daemonAccessTokens.display.prompt') }}
			<ITextInput
				class='mt-4'
				:model-value='token'
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
import { ref, type Ref } from 'vue';
import { useI18n } from 'vue-i18n';
import { toast } from 'vue3-toastify';

const i18n = useI18n();
const show: Ref<boolean> = ref(false);
let token: string | null = null;

function copyToClipboard(): void {
	if (token === null) {
		toast.error(
			i18n.t('components.accessControl.daemonAccessTokens.display.copyFailed'),
		);
		return;
	}
	navigator.clipboard.writeText(token!);
	toast.success(
		i18n.t('components.accessControl.daemonAccessTokens.display.copySuccess'),
	);
}

function open(value: string): void {
	token = value;
	show.value = true;
}

function close(): void {
	show.value = false;
	token = null;
}

defineExpose({
	open,
});
</script>
