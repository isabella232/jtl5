<script type="application/javascript" defer>
    // <!--
    $(function () {
        const setApplePayStatus = function (status) {

            $.ajax({$applePayCheckURL}, {
                method: 'POST',
                contentType: "application/x-www-form-urlencoded; charset=UTF-8",
                data: {
                    available: status
                }
            });

        }
        setApplePayStatus(window.ApplePaySession && window.ApplePaySession.canMakePayments() ? 1 : 0);
    });
    // -->
</script>