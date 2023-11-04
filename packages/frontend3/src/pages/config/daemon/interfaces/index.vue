<template>
	<Head>
		<title>{{ $t('pages.configuration.daemon.interfaces.title') }}</title>
	</Head>
	<Disambiguation :links='links' />
</template>

<route lang='yaml'>
name: DaemonInterfaceConfiguration
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
		title: i18n.t('pages.configuration.daemon.interfaces.dpa.title'),
		description: i18n.t('pages.configuration.daemon.interfaces.dpa.description'),
		to: '/config/daemon/interfaces/dpa',
	},
	{
		title: i18n.t('pages.configuration.daemon.interfaces.uart.title'),
		description: i18n.t('pages.configuration.daemon.interfaces.uart.description'),
		to: '/config/daemon/interfaces/uart',
	},
	{
		title: i18n.t('pages.configuration.daemon.interfaces.spi.title'),
		description: i18n.t('pages.configuration.daemon.interfaces.spi.description'),
		to: '/config/daemon/interfaces/spi',
	},
	{
		title: i18n.t('pages.configuration.daemon.interfaces.cdc.title'),
		description: i18n.t('pages.configuration.daemon.interfaces.cdc.description'),
		to: '/config/daemon/interfaces/cdc',
	},
]);
</script>
