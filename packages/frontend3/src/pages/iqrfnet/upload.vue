<template>
	<Head>
		<title>{{ $t('pages.iqrfnet.upload.title') }}</title>
	</Head>
	<HandlerUploader />
</template>

<route lang='yaml'>
name: 'CoordinatorUpload'
</route>

<script lang='ts' setup>
import { Head } from '@unhead/vue/components';

import HandlerUploader from '@/components/iqrfnet/upload/HandlerUploader.vue';
</script>
