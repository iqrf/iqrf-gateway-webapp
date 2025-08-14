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
		<CCard class='p-4'>
			<h1 class='text-center'>
				{{ $t('core.user.verification.title') }}
			</h1>
			<CCardBody v-if='success !== null'>
				<p class='text-center'>
					<span v-if='success'>
						{{ $t('core.user.verification.success') }}
						<vue-countdown
							ref='countdown'
							:auto-start='true'
							:time='10000'
							@end='signIn'
						>
							<template slot-scope='props'>
								{{ $t('core.user.verification.redirect', {countdown: $tc('time.second', props.seconds)}) }}
							</template>
						</vue-countdown>
					</span>
					<span v-else>
						{{ $t('core.user.verification.failed', {error: verifyError}) }}
					</span>
				</p>
			</CCardBody>
		</CCard>
	</div>
</template>

<script lang='ts'>
import {CCard, CCardBody, CCardHeader} from '@coreui/vue/src';
import VueCountdown from '@chenfengyuan/vue-countdown';
import {Component, Prop, Vue} from 'vue-property-decorator';

import {User, UserRole} from '@/services/AuthenticationService';
import UserService from '@/services/UserService';
import {ErrorResponse} from '@/types';
import { AxiosError } from 'axios';

@Component({
	components: {
		CCard,
		CCardBody,
		CCardHeader,
		VueCountdown,
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

	/**
	 * User entity
	 * @private
	 */
	private user: User|null = null;

	/**
	 * @var {string} verifyError Verification error message
	 */
	private verifyError = '';

	/**
	 * Vue lifecycle hook created
	 */
	created(): void {
		this.$store.commit('spinner/SHOW');
		UserService.verify(this.uuid)
			.then((user: User) => {
				this.success = true;
				this.user = user;
				this.$store.dispatch('user/setJwt', this.user);
				this.$store.commit('spinner/HIDE');
			})
			.catch((error: AxiosError) => {
				this.verifyError = error.response ? (error.response.data as ErrorResponse).message : error.message;
				this.success = false;
				this.$store.commit('spinner/HIDE');
			});
	}

	/**
	 * Signs in the user
	 * @private
	 */
	private signIn(): void {
		if (this.user === null) {
			return;
		}
		if (this.user.role === UserRole.BASIC) {
			location.pathname = '/';
		}
		this.$router.push('/');
	}

}
</script>
