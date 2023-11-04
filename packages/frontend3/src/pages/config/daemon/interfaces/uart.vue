<template>
	<Head>
		<title>{{ $t('pages.configuration.daemon.interfaces.uart.title') }}</title>
	</Head>
	<UartConfig />
</template>

<route lang='yaml'>
name: UartConfiguration
</route>

<script lang='ts' setup>
import { Head } from '@unhead/vue/components';

import UartConfig from '@/components/config/daemon/interfaces/UartConfig.vue';
</script>
