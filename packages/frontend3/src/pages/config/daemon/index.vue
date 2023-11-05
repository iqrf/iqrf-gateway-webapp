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
		title: i18n.t('pages.configuration.daemon.cache.title'),
		description: i18n.t('pages.configuration.daemon.cache.description'),
		to: '/config/daemon/cache',
	},
	{
		title: i18n.t('pages.configuration.daemon.db.title'),
		description: i18n.t('pages.configuration.daemon.db.description'),
		to: '/config/daemon/database',
	},
]);
</script>
