<template>
	<IModalWindow
		v-model='show'
		persistent
	>
		<ICard>
			<template #title>
				{{ $t('components.iqrfnet.network-manager.rf-signal.modal.title') }}
			</template>
			<IDataTable
				v-if='result'
				:headers='headers'
				:items='result'
				:hover='true'
				:dense='true'
				disable-column-filtering
				disable-search
			>
				<template #item.online='{ item }'>
					<IBooleanIcon
						:value='item.online'
					/>
				</template>
				<template #item.counter='{ item }'>
					<span v-if='item.counter !== undefined'>
						{{ item.counter }}
					</span>
					<span v-else>
						{{ $t('common.labels.notAvailable') }}
					</span>
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
import { PropType, ref, Ref } from 'vue';
import { useI18n } from 'vue-i18n';

import { RfSignalTestResult } from '@/types/DaemonApi/Iqmesh';

const componentProps = defineProps({
	result: {
		type: [Object, null] as PropType<RfSignalTestResult[] | null>,
		required: false,
		default: null,
	},
});
defineExpose({
	open,
});
const i18n = useI18n();
const show: Ref<boolean> = ref(false);
const headers = [
	{
		key: 'deviceAddr',
		title: i18n.t('components.iqrfnet.network-manager.rf-signal.modal.address'),
		width: '33%',
	},
	{
		key: 'online',
		title: i18n.t('components.iqrfnet.network-manager.rf-signal.modal.online'),
		filterable: false,
		width: '34%',
	},
	{
		key: 'counter',
		title: i18n.t('components.iqrfnet.network-manager.rf-signal.modal.counter'),
		filterable: false,
		width: '33%',
	},
];

function open(): void {
	show.value = true;
}

function close(): void {
	show.value = false;
}

</script>
