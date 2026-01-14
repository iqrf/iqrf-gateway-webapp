<template>
	<ICard>
		<template #title>
			{{ $t('pages.iqrfnet.enumeration.title') }}
		</template>
		<template #titleActions>
			<IActionBtn
				:action='Action.Reload'
				container-type='card-title'
				:loading='[ComponentState.Loading, ComponentState.Reloading].includes(componentState)'
				@click='enumerate()'
			/>
		</template>
		<v-alert
			v-if='componentState === ComponentState.FetchFailed'
			type='error'
			variant='tonal'
			:text='$t("components.iqrfnet.enumeration.messages.failed")'
		/>
		<v-row>
			<v-col>
				<v-skeleton-loader
					class='table-compact-skeleton-loader'
					:loading='componentState === ComponentState.Loading'
					:type='SkeletonLoaders.simpleTableSkeletonLoader(6)'
				>
					<v-responsive>
						<v-table
							v-if='data'
							class='simple-table'
							density='compact'
						>
							<caption>
								<h3>{{ $t('components.iqrfnet.enumeration.device') }}</h3>
							</caption>
							<tbody>
								<tr>
									<th>{{ $t('components.iqrfnet.common.deviceAddr') }}</th>
									<td>{{ data.deviceAddr }}</td>
								</tr>
								<tr>
									<th>{{ $t('components.iqrfnet.common.hwpid') }}</th>
									<td>{{ `${data.peripheralEnumeration.hwpId.toString(16).padStart(4, '0').toUpperCase()} [${data.peripheralEnumeration.hwpId}]` }}</td>
								</tr>
								<tr>
									<th>{{ $t('components.iqrfnet.common.hwpidVer') }}</th>
									<td>{{ `${data.peripheralEnumeration.hwpIdVer & 0x00_FF}.${data.peripheralEnumeration.hwpIdVer & 0xFF_00}` }}</td>
								</tr>
								<tr v-if='product !== null || data.manufacturer.length > 0'>
									<th>{{ $t('components.iqrfnet.common.manufacturer') }}</th>
									<td v-if='product !== null'>
										{{ product.companyName }}
									</td>
									<td v-else>
										{{ data.manufacturer }}
									</td>
								</tr>
								<tr>
									<th>{{ $t('components.iqrfnet.common.product') }}</th>
									<td v-if='product !== null'>
										<a
											:href='product.homePage'
											target='_blank'
											rel='noopener noreferrer'
										>
											{{ product.name }}
										</a>
									</td>
									<td v-else-if='data.product.length > 0'>
										{{ data.product }}
									</td>
									<td v-else>
										{{ $t('components.iqrfnet.enumeration.uncertified') }}
									</td>
								</tr>
								<tr v-if='product !== null'>
									<th>{{ $t('components.iqrfnet.common.picture') }}</th>
									<td>
										<v-img
											:src='product.picture'
											max-width='100%'
											cover
										/>
									</td>
								</tr>
							</tbody>
						</v-table>
					</v-responsive>
				</v-skeleton-loader>
			</v-col>
			<v-col>
				<v-skeleton-loader
					class='table-compact-skeleton-loader'
					:loading='componentState === ComponentState.Loading'
					:type='SkeletonLoaders.simpleTableSkeletonLoader(6)'
				>
					<v-responsive>
						<v-table
							v-if='data'
							class='simple-table'
							density='compact'
						>
							<caption>
								<h3>{{ $t('components.iqrfnet.enumeration.tr') }}</h3>
							</caption>
							<tbody>
								<tr>
									<th>{{ $t('components.iqrfnet.common.trType') }}</th>
									<td>{{ data.osRead.trMcuType.trType }}</td>
								</tr>
								<tr>
									<th>{{ $t('components.iqrfnet.common.mid') }}</th>
									<td>{{ `${data.osRead.mid} [${Number.parseInt(data.osRead.mid, 16)}]` }}</td>
								</tr>
								<tr>
									<th>{{ $t('components.iqrfnet.common.osVersion') }}</th>
									<td>{{ `${data.osRead.osVersion} (${data.osRead.osBuild})` }}</td>
								</tr>
								<tr>
									<th>{{ $t('components.iqrfnet.common.dpaVersion') }}</th>
									<td>{{ data.peripheralEnumeration.dpaVer }}</td>
								</tr>
								<tr>
									<th>{{ $t('components.iqrfnet.common.rfMode') }}</th>
									<td v-if='data.peripheralEnumeration.flags.rfModeStd'>
										<v-icon
											:icon='mdiPowerPlug'
											color='primary'
											size='x-large'
										/>
										{{ $t('components.iqrfnet.common.rfModes.std') }}
									</td>
									<td v-else-if='data.peripheralEnumeration.flags.rfModeLp'>
										<v-icon
											:icon='mdiBattery70'
											color='primary'
											size='x-large'
										/>
										{{ $t('components.iqrfnet.common.rfModes.lp') }}
									</td>
								</tr>
								<tr>
									<th>{{ $t('components.iqrfnet.common.rssi') }}</th>
									<td>{{ data.osRead.rssi }}</td>
								</tr>
								<tr>
									<th>{{ $t('components.iqrfnet.common.voltage') }}</th>
									<td>{{ data.osRead.supplyVoltage }}</td>
								</tr>
							</tbody>
						</v-table>
					</v-responsive>
				</v-skeleton-loader>
			</v-col>
		</v-row>
	</ICard>
</template>

<script lang='ts' setup>
import { IqmeshServiceMessages } from '@iqrf/iqrf-gateway-daemon-utils/enums';
import { EnumerationService } from '@iqrf/iqrf-gateway-daemon-utils/services/iqmesh';
import { DaemonApiResponse } from '@iqrf/iqrf-gateway-daemon-utils/types';
import { DaemonMessageOptions } from '@iqrf/iqrf-gateway-daemon-utils/utils';
import { ProductService } from '@iqrf/iqrf-repository-client/services';
import { Product } from '@iqrf/iqrf-repository-client/types';
import { Action, ComponentState, IActionBtn, ICard, SkeletonLoaders } from '@iqrf/iqrf-vue-ui';
import { mdiBattery70, mdiPowerPlug } from '@mdi/js';
import { onBeforeMount, onBeforeUnmount, onMounted, ref, Ref } from 'vue';
import { useI18n } from 'vue-i18n';
import { toast } from 'vue3-toastify';

import { useRepositoryClient } from '@/services/RepositoryClient';
import { useDaemonStore } from '@/store/daemonSocket';
import { DeviceEnumeration } from '@/types/DaemonApi/Iqmesh';

const componentProps = defineProps<{
	address: number;
}>();
const componentState: Ref<ComponentState> = ref(ComponentState.Created);
const i18n = useI18n();
const daemonStore = useDaemonStore();
const msgId: Ref<string | null> = ref(null);
const data: Ref<DeviceEnumeration | null> = ref(null);
const productService: Ref<ProductService | null> = ref(null);
const product: Ref<Product | null> = ref(null);

daemonStore.$onAction(({ name, after }) => {
	if (name === 'onMessage') {
		after((rsp: DaemonApiResponse) => {
			if (rsp.data.msgId !== msgId.value) {
				return;
			}
			daemonStore.removeMessage(msgId.value);
			if (rsp.mType === IqmeshServiceMessages.Enumerate) {
				handleEnumerate(rsp);
			}
		});
	}
});

async function enumerate(): Promise<void> {
	componentState.value = [
		ComponentState.Created,
		ComponentState.FetchFailed,
	].includes(componentState.value) ? ComponentState.Loading : ComponentState.Reloading;
	const opts = new DaemonMessageOptions(
		null,
		90_000,
		i18n.t('components.iqrfnet.enumeration.messages.timeout'),
		() => {
			componentState.value = ComponentState.FetchFailed;
			msgId.value = null;
		},
	);
	msgId.value = await daemonStore.sendMessage(
		EnumerationService.enumerate(
			{ repeat: 1, returnVerbose: true },
			{ deviceAddr: componentProps.address },
			opts,
		),
	);
}

async function handleEnumerate(rsp: DaemonApiResponse): Promise<void> {
	if (rsp.data.status !== 0) {
		toast.error(
			i18n.t('components.iqrfnet.enumeration.messages.failed'),
		);
		componentState.value === ComponentState.Loading ? ComponentState.FetchFailed : ComponentState.Ready;
		return;
	}
	data.value = rsp.data.rsp as DeviceEnumeration;
	if ((data.value.peripheralEnumeration.hwpId & 0xF) === 0xF) {
		componentState.value = ComponentState.Ready;
		return;
	}
	if (productService.value === null) {
		componentState.value = ComponentState.Ready;
		return;
	}
	try {
		product.value = await productService.value.get(data.value.peripheralEnumeration.hwpId);
	} catch {
		//
	}
	componentState.value = ComponentState.Ready;
}

onBeforeMount(async () => {
	try {
		productService.value = (await useRepositoryClient()).getProductService();
	} catch {
		//
	}
});

onMounted(() => {
	enumerate();
});

onBeforeUnmount(() => {
	daemonStore.removeMessage(msgId.value);
});
</script>
