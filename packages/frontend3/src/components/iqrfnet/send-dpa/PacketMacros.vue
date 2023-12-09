<template>
	<Card header-color='gray'>
		<template #title>
			{{ $t('components.iqrfnet.send-dpa.macros.title') }}
		</template>
		<v-item-group
			v-if='componentState === ComponentState.Ready'
			class='flex-wrap'
			density='compact'
		>
			<v-menu
				v-for='group of macros'
				:key='group.name'
				location='bottom start'
			>
				<template #activator='{ props }'>
					<v-btn
						v-bind='props'
						color='primary'
						elevation='0'
					>
						{{ group.name }}
						<v-icon :icon='mdiMenuUp' />
					</v-btn>
				</template>
				<v-list
					class='pt-0 pb-0'
					density='compact'
				>
					<v-list-item
						v-for='packet of group.macros'
						:key='packet.name'
						density='compact'
						@click='$emit("set-packet", packet.request)'
					>
						{{ packet.name }}
					</v-list-item>
				</v-list>
			</v-menu>
		</v-item-group>
	</Card>
</template>

<script lang='ts' setup>
import { type DpaMacrosService } from '@iqrf/iqrf-gateway-webapp-client/services/Iqrf';
import { type DpaMacro, type DpaMacroGroup } from '@iqrf/iqrf-gateway-webapp-client/types/Iqrf';
import { mdiMenuUp } from '@mdi/js';
import { type Ref, onMounted, ref } from 'vue';

import Card from '@/components/Card.vue';
import { useApiClient } from '@/services/ApiClient';
import { ComponentState } from '@/types/ComponentState';

defineEmits(['set-packet']);
const componentState: Ref<ComponentState> = ref(ComponentState.Created);
const service: DpaMacrosService = useApiClient().getIqrfServices().getDpaMacrosService();
const macros: Ref<DpaMacroGroup[]> = ref([]);

onMounted(() => {
	componentState.value = ComponentState.Loading;
	service.fetch()
		.then((response: DpaMacroGroup[]) => {
			macros.value = response.filter((group: DpaMacroGroup): boolean => {
				if (!group.enabled) {
					return false;
				}
				group.macros = group.macros.filter((packet: DpaMacro): boolean => packet.enabled);
				return true;
			});
			componentState.value = ComponentState.Ready;
		})
		.catch(() => componentState.value = ComponentState.FetchFailed);
});
</script>

<style lang='scss' scoped>
.v-item-group {
	.v-btn {
		border-radius: 0%;
	}

	.v-btn:first-child {
		border-top-left-radius: 4px;
		border-bottom-left-radius: 4px;
	}

	.v-btn:last-child {
		border-top-right-radius: 4px;
		border-bottom-right-radius: 4px;
	}
}
</style>
