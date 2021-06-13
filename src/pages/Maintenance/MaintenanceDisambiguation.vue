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
		<h1>{{ $t('maintenance.title') }}</h1>
		<CCard body-wrapper>
			<CListGroup>
				<CListGroupItem
					v-if='$store.getters["features/isEnabled"]("pixla")'
					to='/maintenance/pixla/'
				>
					<header class='list-group-item-heading'>
						{{ $t('maintenance.pixla.title') }}
					</header>
					<p class='list-group-item-text'>
						{{ $t('maintenance.pixla.description') }}
					</p>
				</CListGroupItem>
				<CListGroupItem
					v-if='$store.getters["features/isEnabled"]("mender")'
					to='/maintenance/mender/'
				>
					<header class='list-group-item-heading'>
						{{ $t('maintenance.mender.title') }}
					</header>
					<p class='list-group-item-text'>
						{{ $t('maintenance.mender.description') }}
					</p>
				</CListGroupItem>
				<CListGroupItem
					v-if='$store.getters["features/isEnabled"]("monit")'
					to='/maintenance/monit/'
				>
					<header class='list-group-item-heading'>
						{{ $t('maintenance.monit.title') }}
					</header>
					<p class='list-group-item-text'>
						{{ $t('maintenance.monit.description') }}
					</p>
				</CListGroupItem>
			</CListGroup>
		</CCard>
	</div>
</template>

<script lang='ts'>
import {Options, Vue} from 'vue-property-decorator';
import {CCard, CListGroup, CListGroupItem} from '@coreui/vue/src';

import {NavigationGuardNext} from 'vue-router';

@Options({
	components: {
		CCard,
		CListGroup,
		CListGroupItem,
	},
	beforeRouteEnter(to, from, next: NavigationGuardNext): void {
		next((vm: Vue) => {
			if (!vm.$store.getters['features/isEnabled']('mender') &&
				!vm.$store.getters['features/isEnabled']('monit') &&
				!vm.$store.getters['features/isEnabled']('pixla')) {
				vm.$toast.error(vm.$t('maintenance.disabled').toString());
				vm.$router.push(from.path);
			}
		});
	},
	metaInfo: {
		title: 'maintenance.title',
	},
})

/**
 * Maintenance disambiguation component
 */
export default class MaintenanceDisambiguation extends Vue {
}
</script>
