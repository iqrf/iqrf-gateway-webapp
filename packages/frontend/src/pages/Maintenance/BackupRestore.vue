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
	<div>
		<h1>{{ $t('maintenance.backup.title') }}</h1>
		<GatewayBackup class='mb-5' />
		<v-card>
			<v-card-title>{{ $t('maintenance.backup.restoreTitle') }}</v-card-title>
			<GatewayRestore />
		</v-card>
	</div>
</template>

<script lang='ts'>
import {Component, Vue} from 'vue-property-decorator';
import GatewayBackup from '@/components/Maintenance/GatewayBackup.vue';
import GatewayRestore from '@/components/Maintenance/GatewayRestore.vue';

@Component({
	components: {
		GatewayBackup,
		GatewayRestore,
	},
	metaInfo: {
		title: 'maintenance.backup.title',
	},
})

/**
 * Gateway backup component
 */
export default class BackupRestore extends Vue {}
</script>
