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
		<v-container fluid class='fill-height'>
			<v-row align='center' justify='center'>
				<v-col
					cols='12'
					sm='8'
					md='6'
					lg='4'
				>
					<v-img :alt='title' :src='logo' max-height='32pt' contain class='mb-6' />
					<slot />
				</v-col>
			</v-row>
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
