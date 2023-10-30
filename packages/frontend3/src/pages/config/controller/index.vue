<template>
	<Head>
		<title>{{ $t('pages.configuration.controller.title') }}</title>
	</Head>
	<Disambiguation :links='links' />
</template>

<route lang='yaml'>
name: ControllerConfiguration
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
		title: i18n.t('pages.configuration.controller.settings.title'),
		description: i18n.t('pages.configuration.controller.settings.description'),
		to: '/config/controller/settings',
	},
	{
		title: i18n.t('pages.configuration.controller.profiles.title'),
		description: i18n.t('pages.configuration.controller.profiles.description'),
		to: '/config/controller/profiles',
	},
]);
</script>
