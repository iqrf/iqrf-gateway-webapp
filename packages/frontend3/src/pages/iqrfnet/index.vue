<template>
	<Head>
		<title>{{ $t('pages.iqrfnet.title') }}</title>
	</Head>
	<Disambiguation :links='links' />
</template>

<route>
{
	"name": "Iqrfnet",
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
		title: i18n.t('pages.iqrfnet.send-dpa.title'),
		description: i18n.t('pages.iqrfnet.send-dpa.description'),
		to: '/iqrfnet/send-dpa',
	},
	{
		title: i18n.t('pages.iqrfnet.send-json.title'),
		description: i18n.t('pages.iqrfnet.send-json.description'),
		to: '/iqrfnet/send-json',
	},
	{
		title: i18n.t('pages.iqrfnet.upload.title'),
		description: i18n.t('pages.iqrfnet.upload.description'),
		to: '/iqrfnet/upload',
	},
	{
		title: i18n.t('pages.iqrfnet.tr-config.title'),
		description: i18n.t('pages.iqrfnet.tr-config.description'),
		to: '/iqrfnet/tr-config',
	},
]);
</script>
