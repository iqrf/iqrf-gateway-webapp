<template>
	<IModalWindow
		v-model='show'
		persistent
	>
		<ICard>
			<template #title>
				{{ $t('components.iqrfnet.network-manager.frc-response-time.modal.title') }}
			</template>
			<v-table
				v-if='result'
				density='compact'
			>
				<tbody>
					<tr>
						<th>{{ $t('components.iqrfnet.network-manager.frc-response-time.command') }}</th>
						<td class='text-end'>
							{{ commandLabel }}
						</td>
					</tr>
					<tr>
						<th>{{ $t('components.iqrfnet.network-manager.frc-response-time.modal.inaccessibleNodes') }}</th>
						<td class='text-end'>
							{{ result.inaccessibleNodes }}
						</td>
					</tr>
					<tr>
						<th>{{ $t('components.iqrfnet.network-manager.frc-response-time.modal.unhandledNodes') }}</th>
						<td class='text-end'>
							{{ result.unhandledNodes }}
						</td>
					</tr>
					<tr>
						<th>{{ $t('components.iqrfnet.network-manager.frc-response-time.modal.current') }}</th>
						<td class='text-end'>
							{{ result.currentResponseTime }}
						</td>
					</tr>
					<tr>
						<th>{{ $t('components.iqrfnet.network-manager.frc-response-time.modal.recommended') }}</th>
						<td class='text-end'>
							{{ result.recommendedResponseTime }}
						</td>
					</tr>
				</tbody>
			</v-table>
			<v-divider class='my-2' />
			<IDataTable
				v-if='result'
				:headers='headers'
				:items='result.nodes'
				:hover='true'
				:dense='true'
				disable-column-filtering
				disable-search
			>
				<template #item.responded='{ item }'>
					<IBooleanIcon
						:value='item.responded'
					/>
				</template>
				<template #item.handled='{ item }'>
					<IBooleanIcon
						v-if='item.responded'
						:value='item.handled'
					/>
					<span v-else>
						{{ $t('components.iqrfnet.network-manager.frc-response-time.modal.notAvailable') }}
					</span>
				</template>
				<template #item.responseTime='{ item }'>
					<span v-if='item.responded && item.handled'>{{ item.responseTime }}</span>
					<span v-else>{{ $t('components.iqrfnet.network-manager.frc-response-time.modal.notAvailable') }}</span>
				</template>
			</IDataTable>
			<template #actions>
				<IActionBtn
					:action='Action.Close'
					:disabled='componentState === ComponentState.Action'
					@click='close()'
				/>
			</template>
		</ICard>
	</IModalWindow>
</template>

<script lang='ts' setup>
import { FrcCommand } from '@iqrf/iqrf-gateway-daemon-utils/enums/embed';
import { Action, ComponentState, IActionBtn, IBooleanIcon, ICard, IDataTable, IModalWindow } from '@iqrf/iqrf-vue-ui';
import { computed, ref, type Ref } from 'vue';
import { useI18n } from 'vue-i18n';

import { FrcResponseTimeResult } from '@/types/DaemonApi/Iqmesh';

const componentProps = withDefaults(
	defineProps<{
		result?: FrcResponseTimeResult | null;
	}>(),
	{
		result: null,
	},
);
const componentState: Ref<ComponentState> = ref(ComponentState.Idle);
const i18n = useI18n();
const show: Ref<boolean> = ref(false);
const commandLabel = computed(() => {
	switch (componentProps.result?.command) {
		case FrcCommand.Iqrf2Bits:
			return i18n.t('components.iqrfnet.network-manager.frc-response-time.commands.iqrf2bits');
		case FrcCommand.Iqrf1Byte:
			return i18n.t('components.iqrfnet.network-manager.frc-response-time.commands.iqrf1byte');
		case FrcCommand.Iqrf2Bytes:
			return i18n.t('components.iqrfnet.network-manager.frc-response-time.commands.iqrf2byte');
		case FrcCommand.Iqrf4Bytes:
			return i18n.t('components.iqrfnet.network-manager.frc-response-time.commands.iqrf4byte');
		case FrcCommand.User2Bits:
			return i18n.t('components.iqrfnet.network-manager.frc-response-time.commands.user2bits');
		case FrcCommand.User1Byte:
			return i18n.t('components.iqrfnet.network-manager.frc-response-time.commands.user1byte');
		case FrcCommand.User2Bytes:
			return i18n.t('components.iqrfnet.network-manager.frc-response-time.commands.user2byte');
		default:
			return i18n.t('components.iqrfnet.network-manager.frc-response-time.commands.user4byte');
	}
});

const headers = [
	{
		key: 'deviceAddr',
		title: i18n.t('components.iqrfnet.network-manager.frc-response-time.modal.table.address'),
		width: '25%',
	},
	{
		key: 'responded',
		title: i18n.t('components.iqrfnet.network-manager.frc-response-time.modal.table.responded'),
		filterable: false,
		width: '25%',
	},
	{
		key: 'handled',
		title: i18n.t('components.iqrfnet.network-manager.frc-response-time.modal.table.responded'),
		filterable: false,
		width: '25%',
	},
	{
		key: 'responseTime',
		title: i18n.t('components.iqrfnet.network-manager.frc-response-time.modal.table.responseTime'),
		width: '25%',
	},
];

function open(): void {
	show.value = true;
}

function close(): void {
	show.value = false;
}

defineExpose({
	open,
});
</script>
