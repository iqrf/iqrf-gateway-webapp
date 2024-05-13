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
		<title>{{ $t('pages.maintenance.mender.title') }}</title>
	</Head>
	<MenderUpdateControls />
	<RemountControls v-if='featureStore.isEnabled(Feature.remount)' class='my-4' />
</template>

<route>
{
	"name": "BackupMender",
	"meta": {
		"feature": "mender",
	},
}
</route>

<script lang='ts' setup>
import { Feature } from '@iqrf/iqrf-gateway-webapp-client/types';
import { Head } from '@unhead/vue/components';

import MenderUpdateControls from '@/components/maintenance/mender-update/MenderUpdateControls.vue';
import RemountControls from '@/components/maintenance/mender-update/RemountControls.vue';
import { useFeatureStore } from '@/store/features';

const featureStore = useFeatureStore();
</script>
