<template>
	<Head>
		<title>{{ $t('pages.gateway.mode.title') }}</title>
	</Head>
	<ModeTable />
</template>

<route lang='yaml'>
name: 'GatewayMode'
</route>

<script lang='ts' setup>
import { Head } from '@unhead/vue/components';
import ModeTable from '@/components/gateway/mode/ModeTable.vue';
</script>
