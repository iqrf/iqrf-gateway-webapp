<template>
	<Head>
		<title>{{ $t('account.profile.title') }}</title>
	</Head>
	<ProfileEditForm />
	<PasswordChangeForm />
</template>

<route lang='yaml'>
name: Profile
</route>

<script lang='ts' setup>
import { Head } from '@vueuse/head';
import ProfileEditForm from '@/components/account/ProfileEditForm.vue';
import PasswordChangeForm from '@/components/account/PasswordChangeForm.vue';
</script>
