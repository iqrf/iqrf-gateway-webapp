<template>
	<ModalWindow v-model='show'>
		<Card>
			<template #title>
				{{ $t('components.accessControl.apiKeys.display.title') }}
			</template>
			{{ $t('components.accessControl.apiKeys.display.prompt') }}
			<TextInput
				class='mt-4'
				:model-value='apiKey'
				readonly
				variant='solo-filled'
				density='compact'
			>
				<template #append>
					<v-btn
						color='success'
						@click='copyToClipboard'
					>
						<v-icon :icon='mdiClipboard' />
						{{ $t('common.buttons.clipboard') }}
					</v-btn>
				</template>
			</TextInput>
			<template #actions>
				<v-spacer />
				<v-btn
					color='grey-darken-2'
					variant='elevated'
					@click='close'
				>
					<v-icon :icon='mdiWindowClose' />
					{{ $t('common.buttons.close') }}
				</v-btn>
			</template>
		</Card>
	</ModalWindow>
</template>

<script lang='ts' setup>
import { mdiClipboard, mdiWindowClose } from '@mdi/js';
import { type PropType, type Ref, ref } from 'vue';
import { useI18n } from 'vue-i18n';
import { toast } from 'vue3-toastify';

import Card from '@/components/Card.vue';
import ModalWindow from '@/components/ModalWindow.vue';
import TextInput from '@/components/TextInput.vue';

const componentProps = defineProps({
	apiKey: {
		type: [String, null] as PropType<string | null>,
		default: null,
		required: false,
	},
});
defineExpose({
	open,
});
const emit = defineEmits(['closed']);
const show: Ref<boolean> = ref(false);
const i18n = useI18n();

function copyToClipboard(): void {
	navigator.clipboard.writeText(componentProps.apiKey!);
	toast.success(
		i18n.t('components.accessControl.apiKeys.display.copyMessage'),
	);
}

function open(): void {
	show.value = true;
}

function close(): void {
	show.value = false;
	emit('closed');
}
</script>
