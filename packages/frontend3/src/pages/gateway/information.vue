<template>
	<Head>
		<title>{{ $t('pages.gateway.information.title') }}</title>
	</Head>
	<GatewayInfoTable />
</template>

<route lang='yaml'>
name: 'GatewayInformation'
</route>

<script lang='ts' setup>
import { Head } from '@unhead/vue/components';

import GatewayInfoTable from '@/components/gateway/info/GatewayInfoTable.vue';
</script>
