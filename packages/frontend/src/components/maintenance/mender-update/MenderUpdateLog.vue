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
		<template #activator='{ props }'>
			<IActionBtn
				v-if='log !== null'
				v-bind='props'
				:action='Action.Custom'
				:icon='mdiText'
				:text='$t("components.maintenance.mender.update.log.button")'
			/>
		</template>
		<ICard>
			<template #title>
				{{ $t('components.maintenance.mender.update.log.title') }}
			</template>
			<pre class='log'>{{ log }}</pre>
			<template #actions>
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
import { mdiText } from '@mdi/js';
import { type PropType, ref, Ref } from 'vue';

defineProps({
	log: {
		type: [String, null] as PropType<string | null>,
		required: true,
	},
});
const show: Ref<boolean> = ref(false);

const close = () => show.value = false;
</script>
