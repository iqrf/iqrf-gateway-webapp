<template>
	<ModalWindow v-model='showDialog'>
		<template #activator='scope'>
			<template v-if='$slots.activator'>
				<slot name='activator' v-bind='scope || {}' />
			</template>
			<template v-else>
				<v-tooltip
					v-if='tooltip !== null'
					:activator='activator'
					location='bottom'
				>
					{{ tooltip }}
				</v-tooltip>
				<v-icon
					v-bind='scope.props'
					ref='activator'
					color='error'
					size='large'
					:icon='mdiDelete'
				/>
			</template>
		</template>
		<Card header-color='red'>
			<template #title>
				<slot name='title' />
			</template>
			<slot />
			<template #actions>
				<v-btn
					color='red'
					variant='elevated'
					:disabled='componentState === ComponentState.Saving'
					@click='submit()'
				>
					<v-icon :icon='mdiDelete' />
					{{ $t('common.buttons.delete') }}
				</v-btn>
				<v-spacer />
				<v-btn
					color='grey-darken-2'
					variant='elevated'
					:disabled='componentState === ComponentState.Saving'
					@click='close()'
				>
					<v-icon :icon='mdiWindowClose' />
					{{ $t('common.buttons.close') }}
				</v-btn>
			</template>
		</Card>
	</ModalWindow>
</template>

<script setup lang='ts'>
import {mdiDelete, mdiWindowClose} from '@mdi/js';
import {type PropType, ref, type Ref} from 'vue';
import {VIcon} from 'vuetify/components';

import Card from '@/components/Card.vue';
import ModalWindow from '@/components/ModalWindow.vue';
import {ComponentState} from '@/types/ComponentState';

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
/// Activator ref
const activator: Ref<typeof VIcon | null> = ref(null);
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
