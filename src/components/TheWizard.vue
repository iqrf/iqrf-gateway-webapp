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
	<v-main>
		<v-container fluid fill-height>
			<v-layout align-center justify-center>
				<v-flex
					xs12
					sm8
					md6
					lg4
				>
					<div class='logo'>
						<img :alt='title' :src='logo'>
					</div>
					<slot />
				</v-flex>
			</v-layout>
		</v-container>
	</v-main>
</template>

<script lang='ts'>
import {Component, Vue} from 'vue-property-decorator';
import ThemeManager from '@/helpers/themeManager';

/**
 * Installation base page component
 */
@Component
export default class TheWizard extends Vue {

	/**
	 * Returns the logo URL
	 * @returns {string} Log URL
	 */
	private logo = ThemeManager.getWizardLogo();

	/**
	 * Returns the app title
	 * @return {string} App title
	 */
	get title(): string {
		return this.$t(ThemeManager.getTitleKey()).toString();
	}

}
</script>

<style lang='scss' scoped>
.logo {
	padding-bottom: 2rem;

	img {
		height: 32pt;
		width: 100%;
	}
}
</style>
