
$(document).ready(start);

function start() {
    $('#quantity').on('keyup change click', function () {
        var price = $("#price").val();
        var qty = $("#quantity").val();

        var amount = price * qty;
        $(".amountToPayText").html(amount);
        $("#amountToPay").val(amount);
    });
        $('#arrivalDate').datepicker({
        format: 'dd/mm/yyyy',
        todayBtn: true,
        autoclose: true
    });
}
