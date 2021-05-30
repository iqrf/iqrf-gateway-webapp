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
	<CCard>
		<CCardBody>
			{{ $t('install.invitation') }}
		</CCardBody>
		<CCardFooter>
			<CButton color='primary' to='/install/user'>
				{{ $t('install.createUser.title') }}
			</CButton> <CButton color='primary' to='/install/gateway-info'>
				{{ $t('install.gwInfo.button') }}
			</CButton>
		</CCardFooter>
	</CCard>
</template>

<script lang='ts'>
import {Component, Vue} from 'vue-property-decorator';
import {CButton, CCard, CCardBody, CCardFooter} from '@coreui/vue/src';

@Component({
	components: {
		CButton,
		CCard,
		CCardBody,
		CCardFooter
	},
	metaInfo: {
		title: 'install.title'
	},
})

/**
 * Installation disambiguation menu component
 */
export default class InstallationDisambiguation extends Vue {
}
</script>
