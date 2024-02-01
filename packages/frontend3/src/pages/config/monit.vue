<template>
	<Head>
		<title>{{ $t('pages.configuration.monit.title') }}</title>
	</Head>
	<MonitConfig />
</template>

<route>
{
	"name": "MonitConfiguration",
	"meta": {
		"feature": "monit",
	},
}
</route>

<script lang='ts' setup>
import { Head } from '@unhead/vue/components';

import MonitConfig from '@/components/config/monit/MonitConfig.vue';
</script>
