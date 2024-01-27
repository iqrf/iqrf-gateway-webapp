<template>
	<Head>
		<title>{{ $t('pages.ipNetwork.vlan.edit.title') }}</title>
	</Head>
	<ConnectionForm :uuid />
</template>

<route>
{
	"name": "VlanConnectionForm",
	"meta": {
		"feature": "networkManager",
		"roles": ["admin"]
	}
}
</route>

<script lang='ts' setup>
import { Head } from '@unhead/vue/components';
import { validate as uuidValidate, version as uuidVersion } from 'uuid';

import ConnectionForm
	from '@/components/ip-network/connections/ConnectionForm.vue';

const componentProps = defineProps({
	uuid: {
		type: String,
		required: true,
		validator(value: string): boolean {
			return uuidValidate(value) && uuidVersion(value) === 4;
		},
	},
});
</script>
