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
	<div class='c-app'>
		<TheSidebar />
		<CWrapper>
			<TheHeader />
			<div class='c-body'>
				<main class='c-main'>
					<CContainer fluid>
						<div class='fade-in'>
							<slot />
							<router-view />
						</div>
					</CContainer>
				</main>
			</div>
		</CWrapper>
	</div>
</template>

<script lang='ts'>
import {Component, Vue} from 'vue-property-decorator';
import {CContainer, CWrapper} from '@coreui/vue/src';
import TheHeader from './TheHeader.vue';
import TheSidebar from './TheSidebar.vue';

@Component({
	components: {
		CContainer,
		CWrapper,
		TheHeader,
		TheSidebar,
	},
})

/**
 * Dashboard component
 */
export default class TheDashboard extends Vue {}
</script>
