<!--
Copyright 2017-2024 IQRF Tech s.r.o.
Copyright 2019-2024 MICRORISC s.r.o.

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
	<v-card>
		<v-list-item
			v-for='link in linksToRender'
			:key='link.title'
			two-line
			:to='link.to'
			:href='link.href'
			:target='link.target'
		>
			<v-list-item-content>
				<v-list-item-title>{{ link.title }}</v-list-item-title>
				<v-list-item-subtitle>{{ link.description }}</v-list-item-subtitle>
			</v-list-item-content>
		</v-list-item>
	</v-card>
</template>

<script lang='ts'>
import {Component, Prop, Vue} from 'vue-property-decorator';

import DisambiguationHelper, {Link} from '@/helpers/DisambiguationHelper';

@Component({})

/**
 * Disambiguation menu component
 */
export default class Disambiguation extends Vue {

	/**
	 * @property {Array<Link>} Links to render
	 */
	@Prop({required: true}) links!: Array<Link>;

	/**
	 * @var {number} roleIdx Index of role in user role enum
	 */
	get roleIdx() {
		return this.$store.getters['user/getRoleIndex'];
	}

	/**
	 * Returns links for disambiguation menu
	 * @returns {Link[]} Links for disambiguation menu
	 */
	get linksToRender(): Array<Link> {
		return this.links.filter((link: Link) => DisambiguationHelper.filter(link, this.roleIdx));
	}

}
</script>
