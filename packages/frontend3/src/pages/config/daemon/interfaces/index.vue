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
		<title>{{ $t('pages.configuration.daemon.interfaces.title') }}</title>
	</Head>
	<Disambiguation :links='links' />
</template>

<route>
{
	"name": "DaemonInterfaceConfiguration",
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
		title: i18n.t('pages.configuration.daemon.interfaces.dpa.title'),
		description: i18n.t('pages.configuration.daemon.interfaces.dpa.description'),
		to: '/config/daemon/interfaces/dpa',
	},
	{
		title: i18n.t('pages.configuration.daemon.interfaces.uart.title'),
		description: i18n.t('pages.configuration.daemon.interfaces.uart.description'),
		to: '/config/daemon/interfaces/uart',
	},
	{
		title: i18n.t('pages.configuration.daemon.interfaces.spi.title'),
		description: i18n.t('pages.configuration.daemon.interfaces.spi.description'),
		to: '/config/daemon/interfaces/spi',
	},
	{
		title: i18n.t('pages.configuration.daemon.interfaces.cdc.title'),
		description: i18n.t('pages.configuration.daemon.interfaces.cdc.description'),
		to: '/config/daemon/interfaces/cdc',
	},
]);
</script>
