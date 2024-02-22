<template>
	<Head>
		<title>{{ $t('components.errors.403.title') }}</title>
	</Head>
	<Card>
		<template #title>
			{{ $t('components.errors.403.header') }} - {{ $t('components.errors.403.title') }}
		</template>
		<p>{{ $t('components.errors.403.text') }}</p>
	</Card>
</template>

<script setup lang='ts'>
import { Head } from '@unhead/vue/components';

import Card from '@/components/Card.vue';
</script>
