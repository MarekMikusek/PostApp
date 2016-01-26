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
            $("#addAttachment").click(function (event) {
                event.preventDefault();
                $("#attachment").append(
                    "<div class='form-group'>" +
                    "<label for='attachment' class='col-sm-2 control-label'>Attachment</label>" +
                    "<div class='col-sm-10'>" +
                    "<input type='file' class='form-control' name='attachment[]' " +
                    "</div> " +
                    "</div>");
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
<div class="container">
    <form class="form-horizontal" method="post" action="http://postapp.localhost/mail"
          enctype="multipart/form-data">
        <div class="form-group">
            <label for="receiverEmail" class="col-sm-2 control-label">ReceiverEmail</label>
            <div class="col-sm-10">
                <input type="email" class="form-control" id="receiverEmail" name="receiverEmail"
                       placeholder="ReceiverEmail" required>
            </div>
        </div>
        <div class="form-group">
            <label for="subject" class="col-sm-2 control-label">Subject</label>
            <div class="col-sm-10">
                <input type="text" class="form-control" id="subject" name="subject" placeholder="Subject">
            </div>
        </div>
        <div class="form-group">
            <label for="cc" class="col-sm-2 control-label">Cc</label>
            <div class="col-sm-10">
                <input type="email" class="form-control" id="cc" name="cc" placeholder="Cc">
            </div>
        </div>
        <div class="form-group">
            <label for="bcc" class="col-sm-2 control-label">Bcc</label>
            <div class="col-sm-10">
                <input type="email" class="form-control" name="bcc" id="bcc" placeholder="Bcc">
            </div>
        </div>
        <div class="form-group">
            <label for="Bcc" class="col-sm-2 control-label">Message</label>
            <div class="col-sm-10">
                    <textarea rows="5" class="form-control" id="body" name="body"
                              placeholder="Message content"></textarea>
            </div>
        </div>
        <div id="attachment"></div>
        <div class="checkbox col-lg-offset-4">
            <label><input type="checkbox" name="isHtml" value="true">As HTML</label>
        </div>
        <div class="form-group">
            <div class="col-sm-offset-2 col-sm-2">
                <button type="submit" class="btn btn-default" id="addAttachment">Add attachment</button>
            </div>
        </div>
        <div class="form-group">
            <div class="col-sm-offset-2 col-sm-10">
                <button type="submit" class="btn btn-default">Send</button>
            </div>
        </div>
    </form>
</div>
<script src="http://code.jquery.com/jquery.js"></script>
<script src="js/bootstrap.min.js"></script>
</body>
</html>
