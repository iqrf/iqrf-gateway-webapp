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
	<Card>
		<TrConfigSecurityInput
			:model-value='config.accessPassword'
			:access-password='true'
		/>
		<TrConfigSecurityInput
			:model-value='config.securityUserKey'
			:access-password='false'
		/>
	</Card>
</template>

<script lang='ts' setup>
import {
	IqmeshTrConfigParams,
} from '@iqrf/iqrf-gateway-daemon-utils/types/iqmesh';

import TrConfigSecurityInput from '@/components/iqrfnet/tr-config/TrConfigSecurityInput.vue';
import Card from '@/components/layout/card/Card.vue';

const config = defineModel<IqmeshTrConfigParams>('config', { required: true });
</script>
