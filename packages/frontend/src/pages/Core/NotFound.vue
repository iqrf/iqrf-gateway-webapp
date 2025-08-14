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
	<div>
		<h1>{{ $t('core.error.404.title') }}</h1>
		<CCard>
			<CCardHeader>{{ $t('core.error.404.header') }}</CCardHeader>
			<CCardBody>{{ $t('core.error.404.message') }}</CCardBody>
		</CCard>
	</div>
</template>

<script lang='ts'>
import {Component, Vue} from 'vue-property-decorator';
import {CCard, CCardBody, CCardHeader} from '@coreui/vue/src';

@Component({
	components: {
		CCard,
		CCardBody,
		CCardHeader,
	},
	metaInfo: {
		title: 'core.error.404.title',
	},
})

/**
 * Error 404 page component
 */
export default class NotFound extends Vue {
}
</script>
