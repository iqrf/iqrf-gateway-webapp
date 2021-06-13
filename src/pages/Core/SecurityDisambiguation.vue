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
	<div>
		<h1>{{ $t('core.security.title') }}</h1>
		<CCard>
			<CCardBody>
				<CListGroup>
					<CListGroupItem to='/security/api-key/'>
						<header class='list-group-item-heading'>
							{{ $t('core.security.apiKey.title') }}
						</header>
						<p class='list-group-item-text'>
							{{ $t('core.security.apiKey.description') }}
						</p>
					</CListGroupItem>
					<CListGroupItem
						v-if='$store.getters["features/isEnabled"]("ssh")'
						to='/security/ssh-key/'
					>
						<header class='list-group-item-heading'>
							{{ $t('core.security.ssh.title') }}
						</header>
						<p class='list-group-item-text'>
							{{ $t('core.security.ssh.description') }}
						</p>
					</CListGroupItem>
				</CListGroup>
			</CCardBody>
		</CCard>
	</div>
</template>

<script lang='ts'>
import {Options, Vue} from 'vue-property-decorator';
import {CCard, CListGroup, CListGroupItem} from '@coreui/vue/src';

@Options({
	components: {
		CCard,
		CListGroup,
		CListGroupItem
	},
	metaInfo: {
		title: 'core.security.title',
	},
})

/**
 * Main disambiguation menu component
 */
export default class MainDisambiguation extends Vue {
}
</script>
