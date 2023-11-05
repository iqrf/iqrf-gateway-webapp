<!--
Copyright 2017-2023 IQRF Tech s.r.o.
Copyright 2019-2023 MICRORISC s.r.o.

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
	<v-dialog
		v-model='show'
		scrollable
		persistent
		no-click-animation
		:width='width'
	>
		<template #activator='{ props }'>
			<v-btn
				v-bind='props'
				class='mr-1'
				color='error'
			>
				<v-icon :icon='mdiPower' />
				{{ $t('components.gateway.power.powerOff.action') }}
			</v-btn>
		</template>
		<Card header-color='error'>
			<template #title>
				{{ $t('components.gateway.power.powerOff.title') }}
			</template>
			{{ $t('components.gateway.power.powerOff.prompt') }}
			<template #actions>
				<v-btn
					color='error'
					variant='elevated'
					@click='powerOff'
				>
					{{ $t(`components.gateway.power.powerOff.action`) }}
				</v-btn>
				<v-spacer />
				<v-btn
					color='grey-darken-2'
					variant='elevated'
					@click='close'
				>
					{{ $t('common.buttons.cancel') }}
				</v-btn>
			</template>
		</Card>
	</v-dialog>
</template>

<script lang='ts' setup>
import { mdiPower } from '@mdi/js';
import { type Ref, ref } from 'vue';

import Card from '@/components/Card.vue';
import { getModalWidth } from '@/helpers/modal';

const emit = defineEmits(['confirm']);
const show: Ref<boolean> = ref(false);
const width = getModalWidth();

function powerOff(): void {
	emit('confirm');
}

function close(): void {
	show.value = false;
}
</script>
