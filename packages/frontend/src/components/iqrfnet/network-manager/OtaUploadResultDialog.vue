<template>
	<IModalWindow
		v-model='show'
		persistent
	>
		<ICard>
			<template #title>
				{{ modalTitle }}
			</template>
			<IDataTable
				:headers='headers'
				:items='results'
				:hover='true'
				:dense='true'
				disable-column-filtering
				disable-search
			>
				<template #item.result='{ item }'>
					<IBooleanIcon
						:value='item.result'
					/>
				</template>
			</IDataTable>
			<template #actions>
				<IActionBtn
					:action='Action.Close'
					@click='close()'
				/>
			</template>
		</ICard>
	</IModalWindow>
</template>

<script lang='ts' setup>
import { Action, IActionBtn, IBooleanIcon, ICard, IDataTable, IModalWindow } from '@iqrf/iqrf-vue-ui';
import { computed, PropType, ref, Ref } from 'vue';
import { useI18n } from 'vue-i18n';

import { OtaUploadAction, OtaUploadResult } from '@/types/DaemonApi/Iqmesh';

const componentProps = defineProps({
	results: {
		type: Array as PropType<OtaUploadResult[]>,
		required: true,
	},
});
defineExpose({
	open,
});
const i18n = useI18n();
const show: Ref<boolean> = ref(false);
const action: Ref<OtaUploadAction> = ref(OtaUploadAction.Verify);
const headers = [
	{
		key: 'address',
		title: i18n.t('components.iqrfnet.common.deviceAddr'),
	},
	{
		key: 'result',
		title: i18n.t('components.iqrfnet.network-manager.ota-upload.modal.success'),
		align: 'end',
	},
];

const modalTitle = computed(() => {
	if (action.value === OtaUploadAction.Verify) {
		return i18n.t('components.iqrfnet.network-manager.ota-upload.modal.verifyTitle');
	}
	return i18n.t('components.iqrfnet.network-manager.ota-upload.modal.loadTitle');
});

function open(resultAction: OtaUploadAction): void {
	action.value = resultAction;
	show.value = true;
}

function close(): void {
	show.value = false;
}

</script>
