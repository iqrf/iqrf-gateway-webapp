<!--
Copyright 2017-2021 IQRF Tech s.r.o.
Copyright 2019-2021 MICRORISC s.r.o.

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
					<div class='py-5'>
						<LogoBlue :alt='$t("core.title")' width='100%' height='32pt' />
					</div>
					<InstallWizardStepProgress v-if='$route.path !== "/install/gateway-info"' ref='progress' style='justify-content: center;' />
					<router-view @next-step='next' />
				</CCol>
			</CRow>
		</CContainer>
	</div>
</template>

<script lang='ts'>
import {Component, Vue} from 'vue-property-decorator';
import {CContainer, CCol, CRow} from '@coreui/vue/src';
import InstallWizardStepProgress from '../../components/Install/InstallWizardStepProgress.vue';
import LogoBlue from '../../assets/logo-blue.svg';

@Component({
	components: {
		CContainer,
		CCol,
		CRow,
		InstallWizardStepProgress,
		LogoBlue,
	},
})

/**
 * Installation base page component
 */
export default class InstallationBase extends Vue {

	/**
	 * Advance the installation wizard
	 */
	private next(): void {
		(this.$refs.progress as InstallWizardStepProgress).nextStep();
	}
}
</script>
