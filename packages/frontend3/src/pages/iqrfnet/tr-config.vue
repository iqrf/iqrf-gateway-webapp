<template>
	<Head>
		<title>{{ $t('pages.iqrfnet.tr-config.title') }}</title>
	</Head>
	<TrConfigTabs />
</template>

<route>
{
	"name": "TrConfig",
}
</route>

<script lang='ts' setup>
import { Head } from '@unhead/vue/components';

import TrConfigTabs from '@/components/iqrfnet/tr-config/TrConfigTabs.vue';
</script>
