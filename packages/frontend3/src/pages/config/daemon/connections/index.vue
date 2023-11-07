<template>
	<Head>
		<title>{{ $t('pages.configuration.daemon.connections.title') }}</title>
	</Head>
	<Disambiguation :links='links' />
</template>

<route lang='yaml'>
name: DaemonConnectionsConfiguration
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
		title: i18n.t('pages.configuration.daemon.connections.mqtt.title'),
		description: i18n.t('pages.configuration.daemon.connections.mqtt.description'),
		to: '/config/daemon/connections/mqtt',
	},
	{
		title: i18n.t('pages.configuration.daemon.connections.udp.title'),
		description: i18n.t('pages.configuration.daemon.connections.udp.description'),
		to: '/config/daemon/connections/udp',
	},
]);
</script>
