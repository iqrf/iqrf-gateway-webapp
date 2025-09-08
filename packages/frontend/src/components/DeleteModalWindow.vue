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
	<ModalWindow v-model='showDialog'>
		<template #activator='scope'>
			<template v-if='$slots.activator'>
				<slot name='activator' v-bind='scope || {}' />
			</template>
			<template v-else>
				<DataTableAction
					v-bind='scope.props'
					:action='Action.Delete'
					:tooltip='tooltip'
					last
				/>
			</template>
		</template>
		<Card header-color='red'>
			<template #title>
				<slot name='title' />
			</template>
			<slot />
			<template #actions>
				<CardActionBtn
					:action='Action.Delete'
					:disabled='componentState === ComponentState.Saving'
					@click='submit()'
				/>
				<v-spacer />
				<CardActionBtn
					:action='Action.Cancel'
					:disabled='componentState === ComponentState.Saving'
					@click='close()'
				/>
			</template>
		</Card>
	</ModalWindow>
</template>

<script setup lang='ts'>
import { type PropType, ref, type Ref } from 'vue';

import Card from '@/components/layout/card/Card.vue';
import CardActionBtn from '@/components/layout/card/CardActionBtn.vue';
import DataTableAction from '@/components/layout/data-table/DataTableAction.vue';
import ModalWindow from '@/components/ModalWindow.vue';
import { Action } from '@/types/Action';
import { ComponentState } from '@/types/ComponentState';

/// Exposed component functions
defineExpose({
	/// Close dialog window
	close,
});
/// Component props
defineProps({
	/// Component state
	componentState: {
		type: String as PropType<ComponentState>,
		required: false,
		default: ComponentState.Ready,
	},
	/// Tooltip text
	tooltip: {
		type: [String, null] as PropType<string | null>,
		required: false,
		default: null,
	},
});
/// Emit event
const emit = defineEmits(['close', 'submit']);
/// Show dialog window
const showDialog: Ref<boolean> = ref(false);

/**
 * Close dialog window
 */
function close(): void {
	showDialog.value = false;
	emit('close');
}

/**
 * Submit dialog window
 */
function submit(): void {
	emit('submit');
}
</script>
