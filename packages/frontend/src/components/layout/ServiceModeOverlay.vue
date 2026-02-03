<!--
Copyright 2017-2026 IQRF Tech s.r.o.
Copyright 2019-2026 MICRORISC s.r.o.

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
	<IModalWindow
		v-model='show'
		persistent
		no-click-animation
		:scrim='false'
	>
		<ICard
			:action='Action.Custom'
			header-color='warning'
		>
			<template #title>
				{{ $t('components.layout.overlay.serviceMode.title') }}
			</template>
			{{ $t('components.layout.overlay.serviceMode.text') }}
			<template #actions>
				<v-spacer />
				<IActionBtn
					color='warning'
					:to='{ name: "GatewayMode" }'
					:text='$t("components.layout.overlay.serviceMode.button")'
					:icon='mdiWrench'
				/>
				<IActionBtn
					:action='Action.Close'
					@click='show = false'
				/>
			</template>
		</ICard>
	</IModalWindow>
</template>

<script setup lang='ts'>
import { Action, IActionBtn, ICard, IModalWindow } from '@iqrf/iqrf-vue-ui';
import { mdiWrench } from '@mdi/js';
import { ref, type Ref } from 'vue';

const show: Ref<boolean> = ref(true);

</script>
