<template>
	<Head>
		<title>{{ $t('pages.accessControl.apiKeys.title') }}</title>
	</Head>
	<ApiKeyTable />
</template>

<script lang='ts' setup>
import { Head } from '@vueuse/head';
import ApiKeyTable from '@/components/access-control/api-keys/ApiKeyTable.vue';
</script>
