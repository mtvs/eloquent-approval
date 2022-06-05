<template>
	<span class="badge" :class="'bg-'+approvalContext">
		{{ value }}
	</span>
</template>

<script>
	export default {
		props: [
			'value'
		],

		computed: {
			approvalContext() {
				if (this.value == 'approved') {
					return 'success'
				}
				else if(this.value == 'pending') {
					return 'warning'
				}
				else if(this.value == 'rejected') {
					return 'danger'
				}
			}
		}
	}
</script>