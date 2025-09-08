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
			<title>{{ $t('pages.gateway.logs.title') }}</title>
		</Head>
		<LogSelector />
	</div>
</template>

<route>
{
	"name": "GatewayLogs",
}
</route>

<script lang='ts' setup>
import { Head } from '@unhead/vue/components';

import LogSelector from '@/components/gateway/logs/LogSelector.vue';
</script>
