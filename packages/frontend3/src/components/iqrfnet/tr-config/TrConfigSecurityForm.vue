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
import { type TrConfig } from '@iqrf/iqrf-gateway-daemon-utils/types';

import Card from '@/components/Card.vue';
import TrConfigSecurityInput from '@/components/iqrfnet/tr-config/TrConfigSecurityInput.vue';

const config = defineModel<TrConfig>('config', { required: true });
</script>
