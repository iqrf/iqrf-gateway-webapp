<template>
	<Head>
		<title>{{ $t('install.gwInfo.title') }}</title>
	</Head>
	<InstallGatewayInfo />
</template>

<route>
{
	"name": "InstallGwInfo",
	"meta": {
		"layout": "Install",
		"requiresAuth": false,
		"installWizard": true,
	},
}
</route>

<script lang='ts' setup>
import { Head } from '@unhead/vue/components';

import InstallGatewayInfo from '@/components/install/InstallGatewayInfo.vue';
</script>
