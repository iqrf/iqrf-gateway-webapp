<template>
	<Head>
		<title>{{ $t('pages.configuration.controller.profiles.title') }}</title>
	</Head>
	<DeviceProfilesTable />
</template>

<script lang='ts' setup>
import { Head } from '@unhead/vue/components';

import DeviceProfilesTable from '@/components/config/controller/profiles/DeviceProfilesTable.vue';
</script>
