<template>
	<Head>
		<title>{{ $t('pages.iqrfnet.send-dpa.title') }}</title>
	</Head>
	<PacketSender />
</template>

<route lang='yaml'>
name: 'SendDpaPacket'
</route>

<script lang='ts' setup>
import { Head } from '@unhead/vue/components';

import PacketSender from '@/components/iqrfnet/send-dpa/PacketSender.vue';
</script>
