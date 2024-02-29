<template>
	<v-btn
		:color='color'
		:disabled='disabled'
		:prepend-icon='icon'
		variant='elevated'
	>
		{{ text }}
	</v-btn>
</template>

<script lang='ts' setup>
import { mdiClose, mdiDelete, mdiHelp, mdiPencil, mdiPlus } from '@mdi/js';
import { computed, type PropType, type Ref } from 'vue';
import { useI18n } from 'vue-i18n';

import { FormAction } from '@/enums/controls';

const i18n = useI18n();

/** @const componentProps Component properties */
const componentProps = defineProps({
	action: {
		require: true,
		type: String as PropType<FormAction>,
	},
	disabled: {
		default: false,
		required: false,
		type: Boolean,
	},
});

/** @const {Ref<string>} color Form action button color */
const color: Ref<string> = computed((): string => {
	switch (componentProps.action) {
		case FormAction.Add:
			return 'success';
		case FormAction.Cancel:
			return 'grey-darken-2';
		case FormAction.Delete:
			return 'red';
		case FormAction.Edit:
			return 'primary';
		default:
			return 'grey';
	}
});

/** @const {Ref<string>} icon Form action button icon */
const icon: Ref<string> = computed((): string => {
	switch (componentProps.action) {
		case FormAction.Add:
			return mdiPlus;
		case FormAction.Cancel:
			return mdiClose;
		case FormAction.Delete:
			return mdiDelete;
		case FormAction.Edit:
			return mdiPencil;
		default:
			return mdiHelp;
	}
});

/** @const {Ref<string>} text Form action button text */
const text: Ref<string> = computed((): string => {
	switch (componentProps.action) {
		case FormAction.Add:
			return i18n.t('components.common.actions.add').toString();
		case FormAction.Cancel:
			return i18n.t('components.common.actions.cancel').toString();
		case FormAction.Delete:
			return i18n.t('components.common.actions.delete').toString();
		case FormAction.Edit:
			return i18n.t('components.common.actions.edit').toString();
		default:
			return '';
	}
});

</script>
