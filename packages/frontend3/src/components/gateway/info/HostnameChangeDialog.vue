<template>
	<v-dialog
		v-model='show'
		persistent
		scrollable
		no-click-animation
		:width='width'
	>
		<template #activator='{ props }'>
			<v-btn
				color='primary'
				size='x-small'
				v-bind='props'
			>
				<v-icon :icon='mdiPencil' />
				{{ $t('common.buttons.edit') }}
			</v-btn>
		</template>
		<Card>
			<template #title>
				{{ $t('components.gateway.information.hostnameChange.title') }}
			</template>
			<v-form ref='form'>
				<TextInput
					v-model='hostname'
					:label='$t("components.gateway.information.hostname")'
					:rules='[
						(v: string|null) => ValidationRules.required(v, $t("components.gateway.information.hostnameChange.validation.hostname")),
					]'
					required
				/>
				<v-checkbox
					v-model='setSplitterId'
					:label='$t("components.gateway.information.hostnameChange.setSplitterId")'
					density='compact'
				/>
				<v-checkbox
					v-model='setIdeHostname'
					:label='$t("components.gateway.information.hostnameChange.setIdeHostname")'
					density='compact'
				/>
			</v-form>
			<template #actions>
				<v-btn
					color='primary'
					variant='elevated'
					@click='onSubmit'
				>
					{{ $t('common.buttons.save') }}
				</v-btn>
				<v-spacer />
				<v-btn
					color='grey-darken-2'
					variant='elevated'
					@click='show = false'
				>
					{{ $t('common.buttons.cancel') }}
				</v-btn>
			</template>
		</Card>
	</v-dialog>
</template>

<script lang='ts' setup>
import { PropType, ref, Ref, watchEffect } from 'vue';
import { VForm } from 'vuetify/components';

import Card from '@/components/Card.vue';
import TextInput from '@/components/TextInput.vue';

import { mdiPencil } from '@mdi/js';
import { getModalWidth } from '@/helpers/modal';
import { validateForm } from '@/helpers/validateForm';
import ValidationRules from '@/helpers/ValidationRules';


const props = defineProps({
	currentHostname: {
		type: [String, null] as PropType<string | null>,
		required: true,
	}
});
const show: Ref<boolean> = ref(false);
const width = getModalWidth();
const form: Ref<typeof VForm | null> = ref(null);
const hostname: Ref<string> = ref('');
const setSplitterId: Ref<boolean> = ref(false);
const setIdeHostname: Ref<boolean> = ref(false);

watchEffect((): void => {
	if (props.currentHostname === null) {
		hostname.value = '';
	} else {
		hostname.value = props.currentHostname.split('.', 1)[0] ?? '';
	}
});

async function onSubmit(): Promise<void> {
	if (!await validateForm(form.value)) {
		return;
	}
}

async function updateSplitterConfig(): Promise<void> {
	if (!setSplitterId.value) {
		return;
	}
}

async function updateIdeCounterpartConfig(): Promise<void> {
	if (!setIdeHostname.value) {
		return;
	}
}

function clear(): void {
	setSplitterId.value = false;
	setIdeHostname.value = false;
}

</script>
