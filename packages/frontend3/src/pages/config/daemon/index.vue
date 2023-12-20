<template>
	<Head>
		<title>{{ $t('pages.configuration.daemon.title') }}</title>
	</Head>
	<Disambiguation :links='links' />
</template>

<route lang='yaml'>
name: DaemonConfiguration
</route>

<script lang='ts' setup>
import { Head } from '@unhead/vue/components';
import { ref, type Ref } from 'vue';
import { useI18n } from 'vue-i18n';

import Disambiguation from '@/components/Disambiguation.vue';
import { type DisambiguationLink } from '@/types/disambiguation';

const i18n = useI18n();
const links: Ref<DisambiguationLink[]> = ref([
	{
		title: i18n.t('pages.configuration.daemon.interfaces.title'),
		description: i18n.t('pages.configuration.daemon.interfaces.description'),
		to: '/config/daemon/interfaces',
	},
	{
		title: i18n.t('pages.configuration.daemon.connections.title'),
		description: i18n.t('pages.configuration.daemon.connections.description'),
		to: '/config/daemon/connections',
	},
	{
		title: i18n.t('pages.configuration.daemon.scheduler.title'),
		description: i18n.t('pages.configuration.daemon.scheduler.description'),
		to: '/config/daemon/scheduler',
	},
	{
		title: i18n.t('pages.configuration.daemon.json-api.title'),
		description: i18n.t('pages.configuration.daemon.json-api.description'),
		to: '/config/daemon/json-api',
	},
	{
		title: i18n.t('pages.configuration.daemon.repository.title'),
		description: i18n.t('pages.configuration.daemon.repository.description'),
		to: '/config/daemon/repository',
	},
	{
		title: i18n.t('pages.configuration.daemon.db.title'),
		description: i18n.t('pages.configuration.daemon.db.description'),
		to: '/config/daemon/database',
	},
	{
		title: i18n.t('pages.configuration.daemon.logging.title'),
		description: i18n.t('pages.configuration.daemon.logging.description'),
		to: '/config/daemon/logging',
	},
]);
</script>
