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
							v-if='data && product'
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
									<td>{{ data.peripheralEnumeration.hwpId }}</td>
								</tr>
								<tr>
									<th>{{ $t('components.iqrfnet.common.hwpidVer') }}</th>
									<td>{{ data.peripheralEnumeration.hwpIdVer }}</td>
								</tr>
								<tr>
									<th>{{ $t('components.iqrfnet.common.manufacturer') }}</th>
									<td>{{ product.companyName }}</td>
								</tr>
								<tr>
									<th>{{ $t('components.iqrfnet.common.product') }}</th>
									<td>
										<a
											:href='product.homePage'
											target='_blank'
										>
											{{ product.name }}
										</a>
									</td>
								</tr>
								<tr>
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
							v-if='data && product'
							class='simple-table'
							density='compact'
						>
							<caption>
								<h3>{{ $t('components.iqrfnet.enumeration.tr') }}</h3>
							</caption>
							<tbody>
								<tr>
									<th>{{ $t('components.iqrfnet.common.trType') }}</th>
									<td />
								</tr>
								<tr>
									<th>{{ $t('components.iqrfnet.common.mid') }}</th>
									<td />
								</tr>
								<tr>
									<th>{{ $t('components.iqrfnet.common.osVersion') }}</th>
									<td />
								</tr>
								<tr>
									<th>{{ $t('components.iqrfnet.common.dpaVersion') }}</th>
									<td />
								</tr>
								<tr>
									<th>{{ $t('components.iqrfnet.common.rfMode') }}</th>
									<td />
								</tr>
								<tr>
									<th>{{ $t('components.iqrfnet.common.rssi') }}</th>
									<td />
								</tr>
								<tr>
									<th>{{ $t('components.iqrfnet.common.voltage') }}</th>
									<td />
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
import { onBeforeMount, onBeforeUnmount, onMounted, ref, Ref } from 'vue';
import { useI18n } from 'vue-i18n';
import { toast } from 'vue3-toastify';

import { useRepositoryClient } from '@/services/RepositoryClient';
import { useDaemonStore } from '@/store/daemonSocket';
import { DeviceEnumeration } from '@/types/DaemonApi/Iqmesh';

const componentState: Ref<ComponentState> = ref(ComponentState.Created);
const componentProps = defineProps({
	address: {
		type: Number,
		required: true,
	},
});
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
