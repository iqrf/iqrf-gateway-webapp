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
			<CardActionBtn
				:action='Action.Custom'
				color='warning'
				:icon='mdiReload'
				:text='$t("components.gateway.power.reboot.action")'
				v-bind='props'
			/>
		</template>
		<Card header-color='warning'>
			<template #title>
				{{ $t('components.gateway.power.reboot.title') }}
			</template>
			{{ $t('components.gateway.power.reboot.prompt') }}
			<template #actions>
				<CardActionBtn
					color='warning'
					:icon='mdiReload'
					:text='$t("components.gateway.power.reboot.action")'
					@click='reboot'
				/>
				<v-spacer />
				<CardActionBtn
					:action='Action.Cancel'
					@click='close'
				/>
			</template>
		</Card>
	</ModalWindow>
</template>

<script lang='ts' setup>
import { mdiReload } from '@mdi/js';
import { ref, type Ref } from 'vue';

import Card from '@/components/layout/card/Card.vue';
import CardActionBtn from '@/components/layout/card/CardActionBtn.vue';
import ModalWindow from '@/components/ModalWindow.vue';
import { Action } from '@/types/Action';

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