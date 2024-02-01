<template>
	<Head>
		<title>{{ $t('pages.configuration.daemon.repository.title') }}</title>
	</Head>
	<IqrfRepositoryConfig />
</template>

<route>
{
	"name": "RepositoryConfiguration",
}
</route>

<script lang='ts' setup>
import { Head } from '@unhead/vue/components';

import IqrfRepositoryConfig from '@/components/config/daemon/repository/IqrfRepositoryConfig.vue';
</script>
