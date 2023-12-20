<template>
	<Head>
		<title>{{ $t('pages.configuration.daemon.json-api.title') }}</title>
	</Head>
	<JsonApiConfig />
</template>

<route lang='yaml'>
name: JsonApiConfiguration
</route>

<script lang='ts' setup>
import { Head } from '@unhead/vue/components';

import JsonApiConfig from '@/components/config/daemon/json-api/JsonApiConfig.vue';
</script>
