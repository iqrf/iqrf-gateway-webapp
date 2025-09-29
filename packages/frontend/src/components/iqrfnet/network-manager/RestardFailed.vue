<template>
	<IModalWindow v-model='show'>
		<ICard header-color='red'>
			<template #title>
				{{ $t('components.iqrfnet.network-manager.devices.restart.title') }}
			</template>
			<div class='text-center'>
				{{ $t('components.iqrfnet.network-manager.devices.restart.text') }}
				<v-divider class='my-2' />
				<div>
					<v-chip
						v-for='(address, i) of devices'
						:key='i'
						:style='{
							width: "5ch",
							"justify-content": "center",
						}'
						color='red'
						variant='tonal'
						label
					>
						{{ address }}
					</v-chip>
				</div>
			</div>
			<template #actions>
				<IActionBtn
					:action='Action.Cancel'
					@click='close()'
				/>
			</template>
		</ICard>
	</IModalWindow>
</template>
<script setup lang='ts'>
import { Action, IActionBtn, ICard, IModalWindow } from '@iqrf/iqrf-vue-ui';
import { computed, ComputedRef, PropType } from 'vue';

const devices = defineModel({
	type: Array as PropType<number[]>,
	required: true,
});

const show: ComputedRef<boolean> = computed(() => {
	return devices.value.length > 0;
});

function close(): void {
	devices.value = [];
}
</script>
