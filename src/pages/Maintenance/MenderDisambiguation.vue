<template>
	<div>
		<h1>{{ $t('maintenance.mender.title') }}</h1>
		<Disambiguation :links='links' />
	</div>
</template>

<script lang='ts'>
import {Component, Vue} from 'vue-property-decorator';
import Disambiguation from '@/components/Disambiguation.vue';
import {Link} from '@/helpers/DisambiguationHelper';
import {UserRoleIndex} from '@/services/AuthenticationService';

import {NavigationGuardNext, Route} from 'vue-router';

@Component({
	components: {
		Disambiguation,
	},
	beforeRouteEnter(_to: Route, from: Route, next: NavigationGuardNext): void {
		next((vm: Vue) => {
			if (!vm.$store.getters['features/isEnabled']('mender')) {
				vm.$toast.error(vm.$t('maintenance.disabled').toString());
				vm.$router.push(from.path);
			}
		});
	},
	metaInfo: {
		title: 'maintenance.mender.title',
	},
})

/**
 * Mender disambiguation component
 */
export default class MenderDisambiguation extends Vue {

	/**
	 * @var {Link[]} links Links for disambiguation menu
	 */
	private links: Array<Link> = [
		{
			title: this.$t('maintenance.mender.service.title').toString(),
			description: this.$t('maintenance.mender.service.description').toString(),
			to: '/maintenance/mender/service/',
			role: UserRoleIndex.ADMIN,
		},
		{
			title: this.$t('maintenance.mender.update.title').toString(),
			description: this.$t('maintenance.mender.update.description').toString(),
			to: '/maintenance/mender/update/',
			role: UserRoleIndex.ADMIN,
		},
	];

}
</script>
