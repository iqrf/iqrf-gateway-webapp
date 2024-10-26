<!--
Copyright 2017-2024 IQRF Tech s.r.o.
Copyright 2019-2024 MICRORISC s.r.o.

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
	<Head>
		<title>{{ $t('pages.account.recovery.title') }}</title>
	</Head>
	<AccountRecoveryConfirmForm :uuid='uuid' />
</template>

<route>
{
	"name": "AccountRecoveryConfirmation",
	"meta": {
		"requiresAuth": false,
	},
}
</route>

<script lang='ts' setup>
import { Head } from '@unhead/vue/components';
import { validate as uuidValidate, version as uuidVersion } from 'uuid';

import AccountRecoveryConfirmForm
	from '@/components/account/AccountRecoveryConfirmForm.vue';

defineProps({
	uuid: {
		type: String,
		required: true,
		validator(value: string): boolean {
			return uuidValidate(value) && uuidVersion(value) === 4;
		},
	},
});
</script>
