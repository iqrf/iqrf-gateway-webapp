<template>
	<Head>
		<title>{{ $t('pages.configuration.daemon.interfaces.spi.title') }}</title>
	</Head>
	<SpiConfig />
</template>

<route>
{
	"name": "SpiConfiguration",
}
</route>

<script lang='ts' setup>
import { Head } from '@unhead/vue/components';

import SpiConfig from '@/components/config/daemon/interfaces/SpiConfig.vue';
</script>
