<template>
	<Head>
		<title>{{ $t('pages.accessControl.sshKeys.title') }}</title>
	</Head>
	<SshKeyTable />
</template>

<route>
{
	"name": "SshKeys",
	"meta": {
		"roles": ["admin"],
	},
}
</route>

<script lang='ts' setup>
import { Head } from '@unhead/vue/components';

import SshKeyTable from '@/components/access-control/ssh-keys/SshKeyTable.vue';
</script>
