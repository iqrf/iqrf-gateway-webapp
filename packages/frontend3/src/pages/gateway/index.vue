<template>
	<Head>
		<title>{{ $t('pages.gateway.title') }}</title>
	</Head>
	<Disambiguation :links='links' />
</template>

<route lang='yaml'>
name: 'Gateway'
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
		title: i18n.t('pages.gateway.services.title'),
		description: i18n.t('pages.gateway.services.description'),
		to: '/gateway/services',
	},
	{
		title: i18n.t('pages.gateway.power.title'),
		description: i18n.t('pages.gateway.power.description'),
		to: '/gateway/power',
	},
]);
</script>
