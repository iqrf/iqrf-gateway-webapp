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
		v-model='show'
		location='start'
	>
		<template #activator='{ props }'>
			<CardTitleActionBtn
				v-bind='props'
				:icon='mdiCloudPlusOutline'
				:tooltip='$t("components.configuration.daemon.connections.mqtt.clouds.add")'
			/>
		</template>
		<v-list density='compact'>
			<AwsCloudConnectionForm @close='close()' @saved='onSaved()' />
			<AzureCloudConnectionForm @close='close()' @saved='onSaved()' />
			<IbmCloudConnectionForm @close='close()' @saved='onSaved()' />
		</v-list>
	</v-menu>
</template>

<script lang='ts' setup>
import { mdiCloudPlusOutline } from '@mdi/js';
import { ref, type Ref } from 'vue';

import AwsCloudConnectionForm from '@/components/config/daemon/connections/mqtt/cloud/AwsCloudConnectionForm.vue';
import AzureCloudConnectionForm from '@/components/config/daemon/connections/mqtt/cloud/AzureCloudConnectionForm.vue';
import IbmCloudConnectionForm from '@/components/config/daemon/connections/mqtt/cloud/IbmCloudConnectionForm.vue';
import CardTitleActionBtn from '@/components/layout/card/CardTitleActionBtn.vue';

const emit = defineEmits(['saved']);
const show: Ref<boolean> = ref(false);

/**
 * Closes the dialog
 */
function close(): void {
	show.value = false;
}

/**
 * Handles the saved event
 */
function onSaved(): void {
	close();
	emit('saved');
}
</script>
