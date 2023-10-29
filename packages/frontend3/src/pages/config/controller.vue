<template>
	<Head>
		<title>{{ $t('pages.configuration.controller.title') }}</title>
	</Head>
	<ControllerConfig />
</template>

<route lang='yaml'>
name: ControllerConfig
</route>

<script lang='ts' setup>
import { Head } from '@unhead/vue/components';

import ControllerConfig from '@/components/config/controller/ControllerConfig.vue';
</script>
