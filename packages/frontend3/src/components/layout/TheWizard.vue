<!--
Copyright 2017-2025 IQRF Tech s.r.o.
Copyright 2019-2025 MICRORISC s.r.o.

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
	<v-main>
		<v-container fluid class='fill-height py-16'>
			<v-row align='center' justify='center'>
				<v-col
					align-self='center'
					cols='12'
					sm='12'
					md='10'
					lg='8'
					xl='6'
				>
					<div class='logo'>
						<v-img :src='Logo' max-height='32pt' />
					</div>
					<slot />
				</v-col>
			</v-row>
		</v-container>
	</v-main>
</template>

<script lang='ts' setup>
import Logo from '@/assets/logo-blue.svg?url';
</script>

<style lang='scss' scoped>
.logo {
	padding-bottom: 4rem;
}
</style>
