<template>
	<Head>
		<title>{{ $t('errors.404.title') }}</title>
	</Head>
	<Card>
		<template #title>
			{{ $t('errors.404.header') }} - {{ $t('errors.404.title') }}
		</template>
		<p>{{ $t('errors.404.text') }}</p>
	</Card>
</template>

<route lang='yaml'>
name: NotFound
meta:
  requiresAuth: false
</route>

<script lang='ts' setup>
import { Head } from '@unhead/vue/components';

import Card from '@/components/Card.vue';
</script>
