<template>
	<Head>
		<title>{{ $t('pages.accessControl.title') }}</title>
	</Head>
	<Disambiguation :links='links' />
</template>

<route lang='yaml'>
name: 'AccessControl'
</route>

<script lang='ts' setup>
import { ref, Ref } from 'vue';
import { useI18n } from 'vue-i18n';
import { Head } from '@vueuse/head';
import Disambiguation from '@/components/Disambiguation.vue';
import { DisambiguationLink } from '@/types/disambiguation';

const i18n = useI18n();
const links: Ref<DisambiguationLink[]> = ref([
	{
		title: i18n.t('pages.accessControl.users.title'),
		description: i18n.t('pages.accessControl.users.description'),
		to: '/access-control/users',
	},
	{
		title: i18n.t('pages.accessControl.apiKeys.title'),
		description: i18n.t('pages.accessControl.apiKeys.description'),
		to: '/access-control/api-keys',
	},
	{
		title: i18n.t('pages.accessControl.sshKeys.title'),
		description: i18n.t('pages.accessControl.sshKeys.description'),
		to: '/access-control/ssh-keys',
	},
]);
</script>
