<template>
	<Head>
		<title>{{ $t('pages.configuration.daemon.scheduler.title') }}</title>
	</Head>
	<TaskTable />
</template>

<route>
{
	"name": "SchedulerConfiguration",
}
</route>

<script lang='ts' setup>
import { Head } from '@unhead/vue/components';

import TaskTable from '@/components/config/daemon/scheduler/TaskTable.vue';
</script>
