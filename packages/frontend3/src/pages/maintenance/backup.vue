<template>
	<Head>
		<title>{{ $t('pages.maintenance.backup.title') }}</title>
	</Head>
	<GatewayBackup class='mb-4' />
	<GatewayRestore />
</template>

<route>
{
	"name": "BackupMaintenance",
}
</route>

<script lang='ts' setup>
import { Head } from '@unhead/vue/components';

import GatewayBackup from '@/components/maintenance/backup/GatewayBackup.vue';
import GatewayRestore from '@/components/maintenance/backup/GatewayRestore.vue';
</script>
