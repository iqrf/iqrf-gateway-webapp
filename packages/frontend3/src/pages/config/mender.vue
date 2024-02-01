<template>
	<Head>
		<title>{{ $t('pages.configuration.mender.title') }}</title>
	</Head>
	<MenderConfig />
</template>

<route>
{
	"name": "MenderConfiguration",
	"meta": {
		"feature": "mender",
	},
}
</route>

<script lang='ts' setup>
import { Head } from '@unhead/vue/components';

import MenderConfig from '@/components/config/mender/MenderConfig.vue';
</script>
