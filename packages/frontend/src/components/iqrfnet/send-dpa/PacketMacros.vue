<!--
Copyright 2017-2025 IQRF Tech s.r.o.
Copyright 2019-2025 MICRORISC s.r.o.

Licensed under the Apache License, Version 2.0 (the "License");
you may not use this file except in compliance with the License.
You may obtain a copy of the License at

    http://www.apache.org/licenses/LICENSE-2.0

Unless required by applicable law or agreed to in writing, software,
distributed under the License is distributed on an "AS IS" BASIS,
WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied
See the License for the specific language governing permissions and
limitations under the License.
-->

<template>
	<ICard header-color='gray'>
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
	</ICard>
</template>

<script lang='ts' setup>
import { type DpaMacrosService } from '@iqrf/iqrf-gateway-webapp-client/services/Iqrf';
import { type DpaMacro, type DpaMacroGroup } from '@iqrf/iqrf-gateway-webapp-client/types/Iqrf';
import { ComponentState, ICard } from '@iqrf/iqrf-vue-ui';
import { mdiMenuUp } from '@mdi/js';
import { onMounted, ref, type Ref } from 'vue';

import { useApiClient } from '@/services/ApiClient';

defineEmits<{
	'set-packet': [packet: string];
}>();
const componentState: Ref<ComponentState> = ref(ComponentState.Created);
const service: DpaMacrosService = useApiClient().getIqrfServices().getDpaMacrosService();
const macros: Ref<DpaMacroGroup[]> = ref([]);

onMounted(async (): Promise<void> => {
	componentState.value = ComponentState.Loading;
	try {
		const response: DpaMacroGroup[] = await service.get();
		macros.value = response.filter((group: DpaMacroGroup): boolean => {
			if (!group.enabled) {
				return false;
			}
			group.macros = group.macros.filter((packet: DpaMacro): boolean => packet.enabled);
			return true;
		});
		componentState.value = ComponentState.Ready;
	} catch {
		componentState.value = ComponentState.FetchFailed;
	}
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
