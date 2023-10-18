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
import { ref, Ref } from 'vue';
import { useI18n } from 'vue-i18n';
import { Head } from '@unhead/vue/components';
import Disambiguation from '@/components/Disambiguation.vue';
import { DisambiguationLink } from '@/types/disambiguation';

const i18n = useI18n();
const links: Ref<DisambiguationLink[]> = ref([
	{
		title: i18n.t('pages.gateway.title'),
		description: i18n.t('pages.gateway.description'),
		to: '/gateway',
	},
	{
		title: i18n.t('pages.configuration.title'),
		description: i18n.t('pages.configuration.description'),
		to: '/configuration',
	},
	{
		title: i18n.t('pages.accessControl.title'),
		description: i18n.t('pages.accessControl.description'),
		to: '/access-control'
	}
]);
</script>
