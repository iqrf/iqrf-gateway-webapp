<template>
	<NotFound />
</template>

<route>
{
	"name": "NotFound",
	"meta": {
		"requiresAuth": false,
	},
}
</route>

<script lang='ts' setup>
import NotFound from '@/components/errors/NotFound.vue';
</script>
