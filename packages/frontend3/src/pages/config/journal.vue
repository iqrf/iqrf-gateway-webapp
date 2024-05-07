<template>
	<Head>
		<title>{{ $t('pages.configuration.journal.title') }}</title>
	</Head>
	<JournalConfig />
</template>

<route>
{
	"name": "JournalConfiguration",
	"meta": {
		"feature": "journal",
	},
}
</route>

<script lang='ts' setup>
import { Head } from '@unhead/vue/components';

import JournalConfig from '@/components/config/journal/JournalConfig.vue';
</script>
