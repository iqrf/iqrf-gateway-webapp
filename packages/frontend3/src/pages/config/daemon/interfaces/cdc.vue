<template>
	<Head>
		<title>{{ $t('pages.configuration.daemon.interfaces.cdc.title') }}</title>
	</Head>
	<CdcConfig />
</template>

<route lang='yaml'>
name: CdcConfiguration
</route>

<script lang='ts' setup>
import { Head } from '@unhead/vue/components';

import CdcConfig from '@/components/config/daemon/interfaces/CdcConfig.vue';
</script>
