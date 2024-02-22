<template>
	<Head>
		<title>{{ $t('components.errors.404.title') }}</title>
	</Head>
	<Card>
		<template #title>
			{{ $t('components.errors.404.header') }} - {{ $t('components.errors.404.title') }}
		</template>
		<p>{{ $t('components.errors.404.text') }}</p>
	</Card>
</template>

<script setup lang='ts'>
import { Head } from '@unhead/vue/components';

import Card from '@/components/Card.vue';
</script>
