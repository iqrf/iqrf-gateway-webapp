<!--
Copyright 2017-2021 IQRF Tech s.r.o.
Copyright 2019-2021 MICRORISC s.r.o.

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
		<Backup class='mb-5' />
		<Restore />
	</div>
</template>

<script lang='ts'>
import {Component, Vue} from 'vue-property-decorator';
import Backup from '@/components/Maintenance/Backup.vue';
import Restore from '@/components/Maintenance/Restore.vue';

@Component({
	components: {
		Backup,
		Restore,
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
