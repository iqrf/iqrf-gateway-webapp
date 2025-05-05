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
			<title>{{ $t('pages.ipNetwork.title') }}</title>
		</Head>
		<Disambiguation :links='links' />
	</div>
</template>

<route>
{
	"name": "Network",
	"meta": {
		"feature": "networkManager",
		"roles": ["admin"]
	}
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
		title: i18n.t('pages.ipNetwork.ethernet.title'),
		description: i18n.t('pages.ipNetwork.ethernet.description'),
		to: '/ip-network/ethernet',
		roles: [UserRole.Admin],
	},
	{
		title: i18n.t('pages.ipNetwork.wireless.title'),
		description: i18n.t('pages.ipNetwork.wireless.description'),
		to: '/ip-network/wireless',
		roles: [UserRole.Admin],
	},
	{
		title: i18n.t('pages.ipNetwork.mobile.title'),
		description: i18n.t('pages.ipNetwork.mobile.description'),
		to: '/ip-network/mobile',
		roles: [UserRole.Admin],
	},
	{
		title: i18n.t('pages.ipNetwork.vlan.title'),
		description: i18n.t('pages.ipNetwork.vlan.description'),
		to: '/ip-network/vlan',
		roles: [UserRole.Admin],
	},
	{
		title: i18n.t('pages.ipNetwork.wireGuard.title'),
		description: i18n.t('pages.ipNetwork.wireGuard.description'),
		to: '/ip-network/wireguard',
		roles: [UserRole.Admin],
	},
]);
</script>
