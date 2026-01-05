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
		v-if='action === Action.Confirm'
		v-model='showDialog'
		persistent
	>
		<template #activator='scope'>
			<IActionBtn
				v-bind='scope.props'
				:text='$t("components.ipNetwork.wireGuard.tunnels.configuration.form.genKeyPair.genKeyPair")'
				color='warning'
				:loading='componentState === ComponentState.Action'
				:icon='mdiAutorenew'
			/>
		</template>
		<ICard header-color='warning'>
			<template #title>
				{{ $t('components.ipNetwork.wireGuard.tunnels.configuration.form.genKeyPair.title') }}
			</template>
			{{ $t('components.ipNetwork.wireGuard.tunnels.configuration.form.genKeyPair.prompt') }}
			<template #actions>
				<IActionBtn
					color='warning'
					container-type='card'
					:icon='mdiAutorenew'
					:loading='componentState === ComponentState.Action'
					:text='$t("components.ipNetwork.wireGuard.tunnels.configuration.form.genKeyPair.genKeyPair")'
					@click='generateKey()'
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
	<IActionBtn
		v-else-if='action === Action.Apply'
		:text='$t("components.ipNetwork.wireGuard.tunnels.configuration.form.genKeyPair.genKeyPair")'
		color='warning'
		:icon='mdiAutorenew'
		:loading='componentState === ComponentState.Action'
		@click='generateKey()'
	/>
</template>

<script setup lang='ts'>
import {
	Action,
	ComponentState,
	IActionBtn,
	ICard,
	IModalWindow,
} from '@iqrf/iqrf-vue-ui';
import { mdiAutorenew } from '@mdi/js';
import { computed, ComputedRef, type PropType, ref, type Ref } from 'vue';


/// Component props
const componentProps = defineProps({
	/// WireGuard tunnel data
	keyExists: {
		type: Boolean as PropType<boolean>,
		required: true,
	},
});
/// Component emits
const emit = defineEmits<{
	generateKey: [];
}>();
/// Action
const action: ComputedRef<Action> = computed((): Action => componentProps.keyExists ? Action.Confirm : Action.Apply);
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

function generateKey(): void {
	emit('generateKey');
}

defineExpose({
	close,
});
</script>
