<template>
	<Head>
		<title>{{ $t('pages.configuration.daemon.db.title') }}</title>
	</Head>
	<IqrfDbConfig />
</template>

<route lang='yaml'>
name: DatabaseConfiguration
</route>

<script lang='ts' setup>
import { Head } from '@unhead/vue/components';

import IqrfDbConfig from '@/components/config/daemon/database/IqrfDbConfig.vue';
</script>
