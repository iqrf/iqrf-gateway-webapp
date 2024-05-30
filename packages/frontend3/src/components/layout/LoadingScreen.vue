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
	<v-empty-state
		:image='Logo'
		:title='$t("components.layout.loadingScreen.message")'
	>
		<template #media>
			<v-img class='mb-6' height='48pt' />
		</template>
		<v-progress-circular
			class='mt-4'
			indeterminate
			color='primary'
			bg-color='on-surface'
			size='96'
			width='8'
		/>
	</v-empty-state>
</template>

<script setup lang='ts'>
import Logo from '@/assets/logo-blue.svg?url';
</script>
