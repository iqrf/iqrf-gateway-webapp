<template>
	<v-icon
		v-tooltip:bottom='$t("components.config.daemon.connections.websocket.messaging.notes.serviceMissing")'
		:icon='mdiAlert'
		color='red'
	/>
</template>

<script lang='ts' setup>
import { mdiAlert } from '@mdi/js';

</script>
