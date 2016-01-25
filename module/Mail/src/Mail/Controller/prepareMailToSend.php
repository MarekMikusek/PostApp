<?php
if ($_SERVER['REQUEST_METHOD'] == "GET") {
    ?>
    <!DOCTYPE html>
    <html>
    <head>
        <title>Email form</title>
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <!-- Bootstrap -->
        <link href="css/bootstrap.min.css" rel="stylesheet" media="screen">
        <script src="http://ajax.googleapis.com/ajax/libs/jquery/2.0.0/jquery.min.js"></script>
        <script type="text/javascript">
            $(document).ready(function () {
                $("#submit_btn").click(function () {

                    var proceed = true;
                    //simple validation at client's end
                    //loop through each field and we simply change border color to red for invalid fields
                    $("#contact_form input[required=true], #contact_form textarea[required=true]").each(function () {
                        $(this).css('border-color', '');
                        if (!$.trim($(this).val())) { //if this field is empty
                            $(this).css('border-color', 'red'); //change border color to red
                            proceed = false; //set do not proceed flag
                        }
                        //check invalid email
                        var email_reg = /^([\w-\.]+@([\w-]+\.)+[\w-]{2,4})?$/;
                        if ($(this).attr("type") == "email" && !email_reg.test($.trim($(this).val()))) {
                            $(this).css('border-color', 'red'); //change border color to red
                            proceed = false; //set do not proceed flag
                        }
                    });

                    if (proceed) //everything looks good! proceed...
                    {
                        //get input field values data to be sent to server
                        post_data = {
                            'user_name': $('input[name=name]').val(),
                            'user_email': $('input[name=email]').val(),
                                                        'subject': $('select[name=subject]').val(),
                            'msg': $('textarea[name=message]').val()
                        };

                        //Ajax post data to server
                        $.post('contact_me.php', post_data, function (response) {
                            if (response.type == 'error') { //load json data from server and output message
                                output = '<div class="error">' + response.text + '</div>';
                            } else {
                                output = '<div class="success">' + response.text + '</div>';
                                //reset values in all input fields
                                $("#contact_form  input[required=true], #contact_form textarea[required=true]").val('');
                                $("#contact_form #contact_body").slideUp(); //hide form after success
                            }
                            $("#contact_form #contact_results").hide().html(output).slideDown();
                        }, 'json');
                    }
                });

                //reset previously set border colors and hide all message on .keyup()
                $("#contact_form  input[required=true], #contact_form textarea[required=true]").keyup(function () {
                    $(this).css('border-color', '');
                    $("#result").slideUp();
                });
            });
        </script>
    </head>
    <body>
    <form class="form-horizontal">
        <div class="form-group">
            <label for="receiverEmail" class="col-sm-2 control-label">Email</label>
            <div class="col-sm-10">
                <input type="email" class="form-control" id="receiverEmail" placeholder="Email">
            </div>
        </div>
        <div class="form-group">
            <label for="subject" class="col-sm-2 control-label">Email</label>
            <div class="col-sm-10">
                <input type="email" class="form-control" id="subject" placeholder="Subject">
            </div>
        </div>
        <div class="form-group">
            <label for="Cc" class="col-sm-2 control-label">Email</label>
            <div class="col-sm-10">
                <input type="email" class="form-control" id="Cc" placeholder="Email">
            </div>
        </div>
        <div class="form-group">
            <label for="Bcc" class="col-sm-2 control-label">Email</label>
            <div class="col-sm-10">
                <input type="email" class="form-control" id="Bcc" placeholder="Email">
            </div>
        </div>
        <div class="form-group">
            <label for="attachment" class="col-sm-2 control-label">Attachment</label>
            <div class="col-sm-10">
                <input type="file" class="form-control" id="attachment" placeholder="Attachment">
            </div>
        </div>
        <div class="form-group">
            <div class="col-sm-offset-2 col-sm-10">
                <button type="submit" class="btn btn-default">Send</button>
            </div>
        </div>
    </form>
    <script src="http://code.jquery.com/jquery.js"></script>
    <script src="js/bootstrap.min.js"></script>
    </body>
    </html>
    <?php
} else {


}
/**
 * Created by PhpStorm.
 * User: Konto
 * Date: 2016-01-25
 * Time: 11:29
 */