<template>
	<Head>
		<title>{{ $t('pages.install.sshKeys.title') }} | {{ $t('pages.install.title') }}</title>
	</Head>
	<SshKeyTable :install='true' />
</template>

<route>
{
	"name": "InstallSshKeys",
	"meta": {
		"layout": "Install",
		"requiresAuth": false,
		"installWizard": true,
	},
}
</route>

<script lang='ts' setup>
import { Head } from '@unhead/vue/components';

import SshKeyTable from '@/components/access-control/ssh-keys/SshKeyTable.vue';
</script>
