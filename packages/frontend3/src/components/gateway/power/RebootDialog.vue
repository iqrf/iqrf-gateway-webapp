<!--
Copyright 2017-2024 IQRF Tech s.r.o.
Copyright 2019-2024 MICRORISC s.r.o.

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
			<v-btn
				v-bind='props'
				color='primary'
			>
				<v-icon :icon='mdiReload' />
				{{ $t('components.gateway.power.reboot.action') }}
			</v-btn>
		</template>
		<Card>
			<template #title>
				{{ $t('components.gateway.power.reboot.title') }}
			</template>
			{{ $t('components.gateway.power.reboot.prompt') }}
			<template #actions>
				<CardActionBtn
					color='primary'
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
import { type Ref, ref } from 'vue';

import Card from '@/components/layout/card/Card.vue';
import CardActionBtn from '@/components/layout/card/CardActionBtn.vue';
import ModalWindow from '@/components/ModalWindow.vue';
import { Action } from '@/types/Action';

const emit = defineEmits(['confirm']);
const show: Ref<boolean> = ref(false);

function reboot(): void {
	emit('confirm');
}

function close(): void {
	show.value = false;
}
</script>
