<template>
	<Head>
		<title>{{ $t('pages.management.apiKeys.title') }}</title>
	</Head>
	<ApiKeyTable />
</template>

<script lang='ts' setup>
import { Head } from '@vueuse/head';
import ApiKeyTable from '@/components/management/api-keys/ApiKeyTable.vue';
</script>
