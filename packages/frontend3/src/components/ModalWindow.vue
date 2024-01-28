<template>
	<v-dialog
		v-model='showDialog'
		persistent
		scrollable
		no-click-animation
		:width='width'
	>
		<slot />
		<template v-for='(_, slot) in $slots' #[slot]='scope'>
			<slot :name='slot' v-bind='scope || {}' />
		</template>
	</v-dialog>
</template>

<script setup lang='ts'>
import { getModalWidth } from '@/helpers/modal';

const showDialog = defineModel({
	required: true,
	type: Boolean,
});
const width = getModalWidth();
</script>
