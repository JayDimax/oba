<script>
    function remitPOS(totalDue) {
        return {
            totalDue: parseFloat(totalDue),
            cashReceived: 0,
            change: 0,
            balance: 0,
            posOpen: false,
            updateChange() {
                const received = parseFloat(this.cashReceived || 0);
                const diff = received - this.totalDue;
                this.change = diff > 0 ? diff : 0;
                this.balance = diff < 0 ? Math.abs(diff) : 0;
            },
            finalizeSubmission() {
                // You can also store balance to hidden input if needed
            }
        };
    }
</script>
<?php /**PATH /home/u355685815/domains/orcasbettingapp.com/resources/views/partials/pos.blade.php ENDPATH**/ ?>