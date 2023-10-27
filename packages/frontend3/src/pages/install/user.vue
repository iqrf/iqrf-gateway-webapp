<template>
	<Head>
		<title>{{ $t('pages.install.user.title') }} | {{ $t('pages.install.title') }}</title>
	</Head>
	<CreateUserForm />
</template>

<route lang='yaml'>
name: InstallUser
meta:
  layout: Install
  requiresAuth: false
  installWizard: true
</route>

<script lang='ts' setup>
import { Head } from '@unhead/vue/components';

import CreateUserForm from '@/components/install/CreateUserForm.vue';
</script>
