<template>
	<Head>
		<title>{{ $t('pages.iqrfnet.enumeration.title') }}</title>
	</Head>
	<DeviceEnumeration
		v-if='deviceAddr !== null'
		:address='deviceAddr'
	/>
</template>

<route>
{
	"name": "DeviceEnumeration",
}
</route>

<script lang='ts' setup>
import { Head } from '@unhead/vue/components';
import { computed } from 'vue';

import DeviceEnumeration from '@/components/iqrfnet/DeviceEnumeration.vue';

const componentProps = defineProps<{
	address: string;
}>();

const deviceAddr = computed(() => {
	const addr = Number.parseInt(componentProps.address);
	if (Number.isNaN(addr)) {
		return null;
	}
	return addr;
});

</script>
