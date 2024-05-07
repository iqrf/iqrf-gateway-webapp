<template>
	<Head>
		<title>{{ $t('pages.ipNetwork.title') }}</title>
	</Head>
	<Disambiguation :links='links' />
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
