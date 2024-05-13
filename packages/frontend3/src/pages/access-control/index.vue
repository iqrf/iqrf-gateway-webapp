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
		<title>{{ $t('pages.accessControl.title') }}</title>
	</Head>
	<Disambiguation :links='links' />
</template>

<route>
{
	"name": "AccessControl",
	"meta": {
		"roles": ["admin", "basicadmin"],
	},
}
</route>

<script lang='ts' setup>
import { UserRole } from '@iqrf/iqrf-gateway-webapp-client/types';
import { Head } from '@unhead/vue/components';
import { ref, type Ref } from 'vue';
import { useI18n } from 'vue-i18n';

import Disambiguation from '@/components/Disambiguation.vue';
import { type DisambiguationLink } from '@/types/disambiguation';

const i18n = useI18n();
const links: Ref<DisambiguationLink[]> = ref([
	{
		title: i18n.t('pages.accessControl.users.title'),
		description: i18n.t('pages.accessControl.users.description'),
		to: '/access-control/users',
		roles: [UserRole.Admin, UserRole.BasicAdmin],
	},
	{
		title: i18n.t('pages.accessControl.apiKeys.title'),
		description: i18n.t('pages.accessControl.apiKeys.description'),
		to: '/access-control/api-keys',
		roles: [UserRole.Admin],
	},
	{
		title: i18n.t('pages.accessControl.sshKeys.title'),
		description: i18n.t('pages.accessControl.sshKeys.description'),
		to: '/access-control/ssh-keys',
		roles: [UserRole.Admin],
	},
]);
</script>
