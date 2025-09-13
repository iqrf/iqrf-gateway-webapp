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
	<ModalWindow
		v-model='show'
	>
		<template #activator='{ props }'>
			<ICardActionBtn
				:action='Action.Custom'
				color='warning'
				:icon='mdiReload'
				:text='$t("components.gateway.power.reboot.action")'
				v-bind='props'
			/>
		</template>
		<ICard header-color='warning'>
			<template #title>
				{{ $t('components.gateway.power.reboot.title') }}
			</template>
			{{ $t('components.gateway.power.reboot.prompt') }}
			<template #actions>
				<ICardActionBtn
					color='warning'
					:icon='mdiReload'
					:text='$t("components.gateway.power.reboot.action")'
					@click='reboot'
				/>
				<v-spacer />
				<ICardActionBtn
					:action='Action.Cancel'
					@click='close'
				/>
			</template>
		</ICard>
	</ModalWindow>
</template>

<script lang='ts' setup>
import { Action, ICard, ICardActionBtn } from '@iqrf/iqrf-vue-ui';
import { mdiReload } from '@mdi/js';
import { ref, type Ref } from 'vue';

import ModalWindow from '@/components/ModalWindow.vue';

/// Event emitter definition
const emit = defineEmits(['confirm']);
/// Dialog visibility
const show: Ref<boolean> = ref(false);

/**
 * Reboots the gateway
 */
function reboot(): void {
	emit('confirm');
	close();
}

/**
 * Closes the dialog window
 */
function close(): void {
	show.value = false;
}
</script>
