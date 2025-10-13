<!--
Copyright 2017-2025 IQRF Tech s.r.o.
Copyright 2019-2025 MICRORISC s.r.o.

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
	<div>
		<Head>
			<title>{{ $t('pages.config.daemon.title') }}</title>
		</Head>
		<Disambiguation :links='links' />
	</div>
</template>

<route>
{
	"name": "DaemonConfiguration",
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
		title: i18n.t('pages.config.daemon.interfaces.title'),
		description: i18n.t('pages.config.daemon.interfaces.description'),
		to: '/config/daemon/interfaces',
	},
	{
		title: i18n.t('pages.config.daemon.connections.title'),
		description: i18n.t('pages.config.daemon.connections.description'),
		to: '/config/daemon/connections',
	},
	{
		title: i18n.t('pages.config.daemon.scheduler.title'),
		description: i18n.t('pages.config.daemon.scheduler.description'),
		to: '/config/daemon/scheduler',
	},
	{
		title: i18n.t('pages.config.daemon.json-api.title'),
		description: i18n.t('pages.config.daemon.json-api.description'),
		to: '/config/daemon/json-api',
	},
	{
		title: i18n.t('pages.config.daemon.repository.title'),
		description: i18n.t('pages.config.daemon.repository.description'),
		to: '/config/daemon/repository',
	},
	{
		title: i18n.t('pages.config.daemon.db.title'),
		description: i18n.t('pages.config.daemon.db.description'),
		to: '/config/daemon/database',
	},
	{
		title: i18n.t('pages.config.daemon.logging.title'),
		description: i18n.t('pages.config.daemon.logging.description'),
		to: '/config/daemon/logging',
	},
	{
		title: i18n.t('pages.config.daemon.data-collecting.title'),
		description: i18n.t('pages.config.daemon.data-collecting.description'),
		to: '/config/daemon/data-collecting',
	},
]);
</script>
