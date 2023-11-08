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
				{{ $t('components.configuration.daemon.connections.mqtt.clouds.add') }}
			</v-tooltip>
		</template>
		<v-list density='compact'>
			<v-list-item @click='showAws = true'>
				<v-list-item-title>
					{{ $t('components.configuration.daemon.connections.mqtt.clouds.aws.title') }}
				</v-list-item-title>
			</v-list-item>
			<v-list-item @click='showAzure = true'>
				<v-list-item-title>
					{{ $t('components.configuration.daemon.connections.mqtt.clouds.azure.title') }}
				</v-list-item-title>
			</v-list-item>
			<v-list-item @click='showIbm = true'>
				<v-list-item-title>
					{{ $t('components.configuration.daemon.connections.mqtt.clouds.ibm.title') }}
				</v-list-item-title>
			</v-list-item>
		</v-list>
	</v-menu>
	<AwsCloudConnectionForm
		:show='showAws'
		@saved='{onSaved(); showAws = false}'
		@close='showAws = false'
	/>
	<AzureCloudConnectionForm
		:show='showAzure'
		@saved='{onSaved(); showAzure = false}'
		@close='showAzure = false'
	/>
	<IbmCloudConnectionForm
		:show='showIbm'
		@saved='{onSaved(); showIbm = false}'
		@close='showIbm = false'
	/>
</template>

<script lang='ts' setup>

import { mdiCloudPlusOutline } from '@mdi/js';
import { type Ref, ref } from 'vue';

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
