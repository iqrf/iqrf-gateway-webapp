<template>
	<Head>
		<title>{{ $t('pages.accessControl.users.title') }}</title>
	</Head>
	<UserTable />
</template>

<route>
{
	"name": "Users",
	"meta": {
		"roles": ["admin", "basicadmin"],
	},
}
</route>

<script lang='ts' setup>
import { Head } from '@unhead/vue/components';

import UserTable from '@/components/access-control/users/UserTable.vue';

</script>
