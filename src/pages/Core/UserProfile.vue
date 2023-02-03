<!--
Copyright 2017-2023 IQRF Tech s.r.o.
Copyright 2019-2023 MICRORISC s.r.o.

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
	<div>
		<h1>{{ $t('core.profile.title') }}</h1>
		<UserProfileForm />
		<UserProfilePassword />
	</div>
</template>

<script lang='ts'>
import {Component, Vue} from 'vue-property-decorator';
import UserProfileForm from '@/components/Core/UserProfileForm.vue';
import UserProfilePassword from '@/components/Core/UserProfilePassword.vue';

@Component({
	components: {
		UserProfileForm,
		UserProfilePassword,
	},
	metaInfo: {
		title: 'core.profile.title',
	},
})

/**
 * User profile page
 */
export default class UserProfile extends Vue {}
</script>
