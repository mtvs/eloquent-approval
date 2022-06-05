<template>
	<div class="btn-group">
		<button type="button"
                class="btn btn-success"
                :disabled="currentStatus == 'approved'"
                @click="approve">Approve</button>

        <button type="button"
                class="btn btn-warning"
                :disabled="currentStatus == 'pending'"
                @click="suspend">Suspend</button>

        <button type="button"
                class="btn btn-danger"
                :disabled="currentStatus == 'rejected'"
                @click="reject">Reject</button>
	</div>
</template>

<script>
	export default {
		props: [
			'currentStatus',
			'approvalUrl'
		],

		methods: {
			approve() {
				this.postApproval('approved')
			},

			suspend() {
				this.postApproval('pending')
			},

			reject() {
				this.postApproval('rejected')
			},

			postApproval(approvalStatus) {
				axios.post(this.approvalUrl, {
					'approval_status': approvalStatus
				}).then(({data}) => {
					this.$emit('approval-changed', {
						'approval_status': data.approval_status,
						'approval_at': data.approval_at
					})
				})
			}
		}
	}	
</script>