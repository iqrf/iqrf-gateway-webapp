<template>
	<Head>
		<title>{{ $t('pages.accessControl.title') }}</title>
	</Head>
	<Disambiguation :links='links' />
</template>

<route>
{
	"name": "AccessControl",
	"meta": {
		"roles": ["admin", "basicadmin"],
	},
}
</route>

<script lang='ts' setup>
import { UserRole } from '@iqrf/iqrf-gateway-webapp-client/types';
import { Head } from '@unhead/vue/components';
import { ref, type Ref } from 'vue';
import { useI18n } from 'vue-i18n';

import Disambiguation from '@/components/Disambiguation.vue';
import { type DisambiguationLink } from '@/types/disambiguation';

const i18n = useI18n();
const links: Ref<DisambiguationLink[]> = ref([
	{
		title: i18n.t('pages.accessControl.users.title'),
		description: i18n.t('pages.accessControl.users.description'),
		to: '/access-control/users',
		roles: [UserRole.Admin, UserRole.BasicAdmin],
	},
	{
		title: i18n.t('pages.accessControl.apiKeys.title'),
		description: i18n.t('pages.accessControl.apiKeys.description'),
		to: '/access-control/api-keys',
		roles: [UserRole.Admin],
	},
	{
		title: i18n.t('pages.accessControl.sshKeys.title'),
		description: i18n.t('pages.accessControl.sshKeys.description'),
		to: '/access-control/ssh-keys',
		roles: [UserRole.Admin],
	},
]);
</script>
