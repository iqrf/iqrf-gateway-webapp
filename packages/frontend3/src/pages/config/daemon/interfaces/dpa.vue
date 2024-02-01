<template>
	<Head>
		<title>{{ $t('pages.configuration.daemon.interfaces.dpa.title') }}</title>
	</Head>
	<DpaConfig class='mb-4' />
	<ActiveInterface />
</template>

<route>
{
	"name": "DpaConfiguration",
}
</route>

<script lang='ts' setup>
import { Head } from '@unhead/vue/components';

import ActiveInterface from '@/components/config/daemon/interfaces/ActiveInterface.vue';
import DpaConfig from '@/components/config/daemon/interfaces/DpaConfig.vue';
</script>
