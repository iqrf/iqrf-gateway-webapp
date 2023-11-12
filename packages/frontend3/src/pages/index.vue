<template>
	<Head>
		<title>{{ $t('title') }}</title>
	</Head>
	<Disambiguation :links='links' />
</template>

<route lang='yaml'>
name: Dashboard
</route>

<script lang='ts' setup>
import { Feature } from '@iqrf/iqrf-gateway-webapp-client/types';
import { Head } from '@unhead/vue/components';
import { ref, type Ref } from 'vue';
import { useI18n } from 'vue-i18n';

import Disambiguation from '@/components/Disambiguation.vue';
import { useFeatureStore } from '@/store/features';
import { type DisambiguationLink } from '@/types/disambiguation';

const i18n = useI18n();
const featureStore = useFeatureStore();
const links: Ref<DisambiguationLink[]> = ref([
	{
		title: i18n.t('pages.gateway.title'),
		description: i18n.t('pages.gateway.description'),
		to: '/gateway',
	},
	{
		title: i18n.t('pages.configuration.title'),
		description: i18n.t('pages.configuration.description'),
		to: '/config',
	},
	{
		title: i18n.t('pages.maintenance.title'),
		description: i18n.t('pages.maintenance.description'),
		to: '/maintenance',
	},
	{
		title: i18n.t('pages.accessControl.title'),
		description: i18n.t('pages.accessControl.description'),
		to: '/access-control',
	},
	{
		title: i18n.t('pages.docs.title'),
		description: i18n.t('pages.docs.description'),
		href: featureStore.getConfiguration(Feature.docs)?.url,
		feature: Feature.docs,
	},
]);
</script>
