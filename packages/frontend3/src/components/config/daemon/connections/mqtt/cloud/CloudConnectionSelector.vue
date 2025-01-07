<!--
Copyright 2017-2024 IQRF Tech s.r.o.
Copyright 2019-2024 MICRORISC s.r.o.

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
	<v-menu
		location='start'
		:close-on-content-click='true'
	>
		<template #activator='{ props }'>
			<v-btn
				id='tooltip-activator'
				v-bind='props'
				:icon='mdiCloudPlusOutline'
			/>
			<v-tooltip
				activator='#tooltip-activator'
				location='bottom'
			>
				{{ $t('components.config.daemon.connections.mqtt.clouds.add') }}
			</v-tooltip>
		</template>
		<v-list density='compact'>
			<v-list-item @click='showAws = true'>
				<v-list-item-title>
					{{ $t('components.config.daemon.connections.mqtt.clouds.aws.title') }}
				</v-list-item-title>
			</v-list-item>
			<v-list-item @click='showAzure = true'>
				<v-list-item-title>
					{{ $t('components.config.daemon.connections.mqtt.clouds.azure.title') }}
				</v-list-item-title>
			</v-list-item>
			<v-list-item @click='showIbm = true'>
				<v-list-item-title>
					{{ $t('components.config.daemon.connections.mqtt.clouds.ibm.title') }}
				</v-list-item-title>
			</v-list-item>
		</v-list>
	</v-menu>
	<AwsCloudConnectionForm
		v-model='showAws'
		@saved='onSaved()'
	/>
	<AzureCloudConnectionForm
		v-model='showAzure'
		@saved='onSaved()'
	/>
	<IbmCloudConnectionForm
		v-model='showIbm'
		@saved='onSaved()'
	/>
</template>

<script lang='ts' setup>
import { mdiCloudPlusOutline } from '@mdi/js';
import { ref, type Ref } from 'vue';

import AwsCloudConnectionForm from '@/components/config/daemon/connections/mqtt/cloud/AwsCloudConnectionForm.vue';
import AzureCloudConnectionForm from '@/components/config/daemon/connections/mqtt/cloud/AzureCloudConnectionForm.vue';
import IbmCloudConnectionForm from '@/components/config/daemon/connections/mqtt/cloud/IbmCloudConnectionForm.vue';

const emit = defineEmits(['saved']);
const showAws: Ref<boolean> = ref(false);
const showAzure: Ref<boolean> = ref(false);
const showIbm: Ref<boolean> = ref(false);

function onSaved(): void {
	close();
	emit('saved');
}
</script>
