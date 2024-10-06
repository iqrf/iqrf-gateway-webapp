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
	<Head>
		<title>{{ $t('pages.config.title') }}</title>
	</Head>
	<Disambiguation :links='links' />
</template>

<route>
{
	"name": "Configuration",
}
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
		title: i18n.t('pages.config.controller.title'),
		description: i18n.t('pages.config.controller.description'),
		to: '/config/controller',
		feature: Feature.iqrfGatewayController,
	},
	{
		title: i18n.t('pages.config.daemon.title'),
		description: i18n.t('pages.config.daemon.description'),
		to: '/config/daemon',
	},
	{
		title: i18n.t('pages.config.influxdb-bridge.title'),
		description: i18n.t('pages.config.influxdb-bridge.description'),
		to: '/config/influxdb-bridge',
		feature: Feature.iqrfGatewayInfluxdbBridge,
	},
	{
		title: i18n.t('pages.config.smtp.title'),
		description: i18n.t('pages.config.smtp.description'),
		to: '/config/smtp',
	},
	{
		title: i18n.t('pages.config.time.title'),
		description: i18n.t('pages.config.time.description'),
		to: '/config/time',
	},
	{
		title: i18n.t('pages.config.journal.title'),
		description: i18n.t('pages.config.journal.description'),
		to: '/config/journal',
		feature: Feature.journal,
	},
	{
		title: i18n.t('pages.config.unattendedUpgrades.title'),
		description: i18n.t('pages.config.unattendedUpgrades.description'),
		to: '/config/unattended-upgrades',
		feature: Feature.unattendedUpgrades,
	},
	{
		title: i18n.t('pages.config.monit.title'),
		description: i18n.t('pages.config.monit.description'),
		to: '/config/monit',
		feature: Feature.monit,
	},
]);
</script>
