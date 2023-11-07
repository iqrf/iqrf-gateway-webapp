<template>
	<Head>
		<title>{{ $t('pages.configuration.title') }}</title>
	</Head>
	<Disambiguation :links='links' />
</template>

<route lang='yaml'>
name: Configuration
</route>

<script lang='ts' setup>
import { Feature } from '@iqrf/iqrf-gateway-webapp-client/types';
import { Head } from '@unhead/vue/components';
import { ref, type Ref } from 'vue';
import { useI18n } from 'vue-i18n';

import Disambiguation from '@/components/Disambiguation.vue';
import { type DisambiguationLink } from '@/types/disambiguation';

const i18n = useI18n();
const links: Ref<DisambiguationLink[]> = ref([
	{
		title: i18n.t('pages.configuration.controller.title'),
		description: i18n.t('pages.configuration.controller.description'),
		to: '/config/controller',
		feature: Feature.iqrfGatewayController,
	},
	{
		title: i18n.t('pages.configuration.daemon.title'),
		description: i18n.t('pages.configuration.daemon.description'),
		to: '/config/daemon',
	},
	{
		title: i18n.t('pages.configuration.smtp.title'),
		description: i18n.t('pages.configuration.smtp.description'),
		to: '/config/smtp',
	},
	{
		title: i18n.t('pages.configuration.time.title'),
		description: i18n.t('pages.configuration.time.description'),
		to: '/config/time',
	},
	{
		title: i18n.t('pages.configuration.journal.title'),
		description: i18n.t('pages.configuration.journal.description'),
		to: '/config/journal',
		feature: Feature.journal,
	},
	{
		title: i18n.t('pages.configuration.unattendedUpgrades.title'),
		description: i18n.t('pages.configuration.unattendedUpgrades.description'),
		to: '/config/unattended-upgrades',
		feature: Feature.unattendedUpgrades,
	},
]);
</script>
