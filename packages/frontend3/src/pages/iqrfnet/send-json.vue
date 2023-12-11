<template>
	<Head>
		<title>{{ $t('pages.iqrfnet.send-json.title') }}</title>
	</Head>
	<JsonSender />
</template>

<route lang='yaml'>
name: 'SendJsonRequest'
</route>

<script lang='ts' setup>
import { Head } from '@unhead/vue/components';

import JsonSender from '@/components/iqrfnet/send-json/JsonSender.vue';
</script>
