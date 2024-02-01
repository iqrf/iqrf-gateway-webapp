<template>
	<Head>
		<title>{{ $t('pages.gateway.title') }}</title>
	</Head>
	<Disambiguation :links='links' />
</template>

<route>
{
	"name": "Gateway",
}
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
		title: i18n.t('pages.gateway.information.title'),
		description: i18n.t('pages.gateway.information.description'),
		to: '/gateway/information',
	},
	{
		title: i18n.t('pages.gateway.logs.title'),
		description: i18n.t('pages.gateway.logs.description'),
		to: '/gateway/logs',
	},
	{
		title: i18n.t('pages.gateway.mode.title'),
		description: i18n.t('pages.gateway.mode.description'),
		to: '/gateway/mode',
	},
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
