<!--
Copyright 2017-2024 IQRF Tech s.r.o.
Copyright 2019-2024 MICRORISC s.r.o.

Licensed under the Apache License, Version 2.0 (the "License");
you may not use this file except in compliance with the License.
You may obtain a copy of the License at

    http://www.apache.org/licenses/LICENSE-2.0

Unless required by applicable law or agreed to in writing, software,
distributed under the License is distributed on an "AS IS" BASIS,
WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied
See the License for the specific language governing permissions and
limitations under the License.
-->

<template>
	<Disambiguation :links='links' />
</template>

<route>
{
	"name": "Dashboard",
}
</route>

<script lang='ts' setup>
import { Feature, UserRole } from '@iqrf/iqrf-gateway-webapp-client/types';
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
		title: i18n.t('pages.iqrfnet.title'),
		description: i18n.t('pages.iqrfnet.description'),
		to: '/iqrfnet',
	},
	{
		title: i18n.t('pages.ipNetwork.title'),
		description: i18n.t('pages.ipNetwork.description'),
		to: '/ip-network',
		roles: [UserRole.Admin],
		feature: Feature.networkManager,
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
		title: i18n.t('pages.dev.title'),
		description: i18n.t('pages.dev.description'),
		to: '/dev',
		developmentOnly: true,
	},
	{
		title: i18n.t('pages.docs.title'),
		description: i18n.t('pages.docs.description'),
		href: featureStore.getConfiguration(Feature.docs)?.url,
		feature: Feature.docs,
	},
]);
</script>
