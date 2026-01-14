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
		v-if='action === Action.Disable'
		v-model='showDialog'
		persistent
	>
		<template #activator='scope'>
			<IDataTableAction
				v-bind='scope.props'
				color='red'
				:icon='disableIcon'
				:tooltip='disableTooltip'
			/>
		</template>
		<ICard header-color='red'>
			<template #title>
				{{ title }}
			</template>
			{{ prompt }}
			<template #actions>
				<IActionBtn
					color='red'
					container-type='card'
					:icon='disableIcon'
					:loading='componentState === ComponentState.Action'
					:text='disableButtonText'
					@click='disable()'
				/>
				<v-spacer />
				<IActionBtn
					:action='Action.Cancel'
					container-type='card'
					:disabled='componentState === ComponentState.Action'
					@click='close()'
				/>
			</template>
		</ICard>
	</IModalWindow>
	<IDataTableAction
		v-else-if='action === Action.Enable'
		color='green'
		:icon='enableIcon'
		:loading='componentState === ComponentState.Action'
		:tooltip='enableTooltip'
		@click='enable()'
	/>
</template>

<script setup lang='ts'>
import {
	Action,
	ComponentState,
	IActionBtn,
	ICard,
	IDataTableAction,
	IModalWindow,
} from '@iqrf/iqrf-vue-ui';
import { computed, type ComputedRef, ref, type Ref } from 'vue';

/// Component props
const componentProps = defineProps<{
	/// WireGuard current state
	enabled: boolean;
	/// Tooltip for disable action
	disableTooltip: string;
	/// Tooltip for enable action
	enableTooltip: string;
	/// Dialog title
	title: string;
	/// Dialog prompt
	prompt: string;
	/// Disable button text
	disableButtonText: string;
	/// Icon for disable action
	disableIcon: string;
	/// Icon for enable action
	enableIcon: string;
}>();
/// Component emits
const emit = defineEmits<{
	disable: [];
	enable: [];
}>();
/// Action
const action: ComputedRef<Action> = computed((): Action => componentProps.enabled ? Action.Disable : Action.Enable);
/// Component state
const componentState: Ref<ComponentState> = ref(ComponentState.Created);
/// Show dialog window
const showDialog: Ref<boolean> = ref(false);

/**
 * Close dialog window
 */
function close(): void {
	showDialog.value = false;
}

function disable(): void {
	emit('disable');
}

function enable(): void {
	emit('enable');
}

defineExpose({
	close,
});
</script>
