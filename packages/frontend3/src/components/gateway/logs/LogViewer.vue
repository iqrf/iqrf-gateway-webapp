<template>
	<Card>
		<template #title>
			{{ $t('pages.gateway.logs.title') }}
		</template>
		<template #titleActions>
			<v-select
				v-model='tab'
				:items='services'
				density='compact'
				variant='outlined'
			/>
			<v-btn
				color='white'
				:icon='mdiFolderDownloadOutline'
			/>
			<v-btn
				color='white'
				:icon='mdiReload'
			/>
		</template>
		<template #extension>
			<v-tabs
				v-model='tab'
				:fixed-tabs='true'
			>
				<v-tab
					v-for='service in services'
					:key='service'
					:value='service'
				>
					{{ service }}
				</v-tab>
			</v-tabs>
		</template>
		<v-tabs
			v-model='tab'
			:fixed-tabs='true'
		>
			<v-tab
				v-for='service in services'
				:key='service'
				:value='service'
			>
				{{ service }}
			</v-tab>
		</v-tabs>
	</Card>
</template>

<script lang='ts' setup>
import { mdiFolderDownloadOutline, mdiReload } from '@mdi/js';
import { type Ref, ref } from 'vue';

import Card from '@/components/Card.vue';

const services = [
	'Controller',
	'Daemon',
	'Setter',
	'Uploader',
	'Journal',
];
const tab: Ref<string> = ref('Controller');
</script>
