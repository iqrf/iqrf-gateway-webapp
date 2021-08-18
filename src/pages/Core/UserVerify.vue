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
		<CCard class='p-4'>
			<h1 class='text-center'>
				{{ $t('core.user.verification.title') }}
			</h1>
			<CCardBody v-if='success !== null'>
				<p class='text-center'>
					{{ success ? $t('core.user.verification.success') : $t('core.user.verification.failed', {error: 'placeholder'}) }}
				</p>
			</CCardBody>
		</CCard>
	</div>
</template>

<script lang='ts'>
import {Component, Prop, Vue} from 'vue-property-decorator';
import {CCard, CCardBody, CCardHeader} from '@coreui/vue/src';
import UserService from '../../services/UserService';

@Component({
	components: {
		CCard,
		CCardBody,
		CCardHeader,
	},
	metaInfo: {
		title: 'core.user.verification.title',
	}
})
export default class UserVerify extends Vue {

	/**
	 * @property {string} uuid User verification UUID
	 */
	@Prop({required: true}) uuid!: string;

	/**
	 * @var {boolean|null} Is the verification successful?
	 */
	private success: boolean|null = null;

	created(): void {
		this.$store.commit('spinner/SHOW');
		UserService.verify(this.uuid)
			.then(() => {
				this.success = true;
				this.$store.commit('spinner/HIDE');
			}).catch(() => {
				this.success = false;
				this.$store.commit('spinner/HIDE');
			});
	}

}
</script>
