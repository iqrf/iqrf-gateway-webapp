<template>
	<Head>
		<title>{{ $t('pages.configuration.daemon.jsonApi.title') }}</title>
	</Head>
	<JsonApiRawConfig class='mb-4' />
	<JsonSplitterConfig />
</template>

<route lang='yaml'>
name: JsonApiConfiguration
</route>

<script lang='ts' setup>
import { Head } from '@unhead/vue/components';

import JsonApiRawConfig from '@/components/config/daemon/json-api/JsonApiRawConfig.vue';
import JsonSplitterConfig from '@/components/config/daemon/json-api/JsonSplitterConfig.vue';
</script>
