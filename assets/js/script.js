$(document).ready(function () {
    $("#formAlert").hide();

    // Button
    $(function() {
        $(".btn").click(function(){
            $(this).button('loading').delay(1000).queue(function() {
                $(this).button('reset');
                $(this).dequeue();
            });
        });
    });

    // Tooltip
    $(function () {
      $('[data-toggle="tooltip"]').tooltip()
    });

    $(".close").click(function(){
        $("#myAlert").alert();
    });

    // Alert Box
    $('form[name="twitterQuery"]').on("submit", function (e) {
        var query = $(this).find('input[name="q"]');
        if ($.trim(query.val()) === "") {
            e.preventDefault();
            $("#formAlert").slideDown(400);
        } else {
            $("#formAlert").slideUp(400);
        }
    });

    $(".alert").find(".close").on("click", function (e) {
        e.stopPropagation();
        e.preventDefault();
        $(this).closest(".alert").slideUp(400);
    });
});