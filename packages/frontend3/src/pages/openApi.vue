<template>
	<Head>
		<title>{{ $t('pages.openApi.title') }}</title>
	</Head>
	<OpenApiSpecification />
</template>

<route>
{
	"name": "OpenApiSpecification",
	"meta": {
		"developmentOnly": true,
		"requiresAuth": false,
	}
}
</route>

<script lang='ts' setup>
import { Head } from '@unhead/vue/components';

import OpenApiSpecification from '@/components/OpenApiSpecification.vue';
</script>
