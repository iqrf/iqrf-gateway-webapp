<template>
	<Head>
		<title>{{ $t('pages.configuration.daemon.cache.title') }}</title>
	</Head>
	<JsCacheConfig />
</template>

<route lang='yaml'>
name: CacheConfiguration
</route>

<script lang='ts' setup>
import { Head } from '@unhead/vue/components';

import JsCacheConfig from '@/components/config/daemon/cache/JsCacheConfig.vue';
</script>
