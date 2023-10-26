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
import { ref, Ref } from 'vue';
import { useI18n } from 'vue-i18n';
import { Head } from '@unhead/vue/components';
import Disambiguation from '@/components/Disambiguation.vue';
import { DisambiguationLink } from '@/types/disambiguation';

const i18n = useI18n();
const links: Ref<DisambiguationLink[]> = ref([
	{
		title: i18n.t('pages.configuration.smtp.title'),
		description: i18n.t('pages.configuration.smtp.description'),
		to: '/config/smtp',
	},
	{
		title: i18n.t('pages.configuration.time.title'),
		description: i18n.t('pages.configuration.time.description'),
		to: '/config/time',
	}
]);
</script>
