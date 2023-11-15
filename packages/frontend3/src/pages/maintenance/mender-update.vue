<template>
	<Head>
		<title>{{ $t('pages.maintenance.mender.title') }}</title>
	</Head>
	<MenderUpdateControls />
	<RemountControls v-if='featureStore.isEnabled(Feature.remount)' class='my-4' />
</template>

<route lang='yaml'>
name: 'BackupMender'
</route>

<script lang='ts' setup>
import { Feature } from '@iqrf/iqrf-gateway-webapp-client/types';
import { Head } from '@unhead/vue/components';

import MenderUpdateControls from '@/components/maintenance/mender-update/MenderUpdateControls.vue';
import RemountControls from '@/components/maintenance/mender-update/RemountControls.vue';
import { useFeatureStore } from '@/store/features';

const featureStore = useFeatureStore();
</script>
