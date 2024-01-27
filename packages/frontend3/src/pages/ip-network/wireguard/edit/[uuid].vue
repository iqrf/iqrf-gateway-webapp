<template>
	<Head>
		<title>{{ $t('pages.ipNetwork.wireGuard.edit.title') }}</title>
	</Head>
</template>

<route>
{
	"name": "WireGuardConnectionForm",
	"meta": {
		"feature": "networkManager",
		"roles": ["admin"]
	}
}
</route>

<script lang='ts' setup>
import { Head } from '@unhead/vue/components';
</script>
