<template>
	<Head>
		<title>{{ $t('pages.accessControl.apiKeys.title') }}</title>
	</Head>
	<ApiKeyTable />
</template>

<route>
{
	"name": "ApiKeys",
	"meta": {
		"roles": ["admin"],
	},
}
</route>

<script lang='ts' setup>
import { Head } from '@unhead/vue/components';

import ApiKeyTable from '@/components/access-control/api-keys/ApiKeyTable.vue';
</script>
