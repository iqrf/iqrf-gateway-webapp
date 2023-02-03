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
	<div class='c-app flex-row align-items-center'>
		<CContainer>
			<CRow class='justify-content-center'>
				<CCol lg='10'>
					<div class='logo'>
						<Logo :alt='title' />
					</div>
					<slot />
				</CCol>
			</CRow>
		</CContainer>
	</div>
</template>

<script lang='ts'>
import {Component, Vue} from 'vue-property-decorator';
import {CContainer, CCol, CRow} from '@coreui/vue/src';
import ThemeManager from '@/helpers/themeManager';

@Component({
	components: {
		CContainer,
		CCol,
		CRow,
		Logo: ThemeManager.getWizardLogo(),
	},
})

/**
 * Installation base page component
 */
export default class TheWizard extends Vue {

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
	padding-bottom: 3rem;
	padding-top: 3rem;

	svg {
		height: 32pt;
		width: 100%;
	}
}

</style>
