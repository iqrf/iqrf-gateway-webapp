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
	<ICard>
		<template #title>
			{{ $t('pages.gateway.power.title') }}
		</template>
		<PowerOffDialog class='mr-2' />
		<RebootDialog />
	</ICard>
</template>

<script lang='ts' setup>
import { ICard } from '@iqrf/iqrf-vue-ui';

import PowerOffDialog from '@/components/gateway/power/PowerOffDialog.vue';
import RebootDialog from '@/components/gateway/power/RebootDialog.vue';
</script>
