<template>
	<Head>
		<title>{{ $t('pages.configuration.time.title') }}</title>
	</Head>
	<TimeConfig />
</template>

<route>
{
	"name": "TimeConfiguration",
}
</route>

<script lang='ts' setup>
import { Head } from '@unhead/vue/components';

import TimeConfig from '@/components/config/time/TimeConfig.vue';
</script>
