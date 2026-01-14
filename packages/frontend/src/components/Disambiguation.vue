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
	<v-list
		class='py-0'
		variant='elevated'
		elevation='1'
		rounded
	>
		<v-list-item
			v-for='link in filteredLinks'
			:key='link.title'
			lines='two'
			:to='link.to'
			:href='link.href'
			:target='link.target'
		>
			<v-list-item-title>{{ link.title }}</v-list-item-title>
			<v-list-item-subtitle>{{ link.description }}</v-list-item-subtitle>
		</v-list-item>
	</v-list>
</template>

<script lang='ts' setup>
import { computed, type ComputedRef } from 'vue';

import { useFeatureStore } from '@/store/features';
import { useUserStore } from '@/store/user';
import { type DisambiguationLink } from '@/types/disambiguation';

const componentProps = defineProps<{
	links: DisambiguationLink[];
}>();
const userStore = useUserStore();
const featureStore = useFeatureStore();
const filteredLinks: ComputedRef<DisambiguationLink[]> = computed(() => {
	return componentProps.links.filter((link: DisambiguationLink) => {
		if (
			((link.developmentOnly ?? false) && import.meta.env.PROD) ||
			(link.roles !== undefined && !link.roles.includes(userStore.getRole!))
		) {
			return false;
		}
		return !(link.feature !== undefined && !featureStore.isEnabled(link.feature));
	});
});
</script>
