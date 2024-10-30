<?php
/**
 * @var string $token
 * @var string $approvalUuid
 */
?>
<!DOCTYPE html>
<html lang="nl">
<head>
    <title>Extra verificatie vereist</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://fonts.googleapis.com/css?family=Poppins&display=swap" rel="stylesheet">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.4.1/jquery.min.js"
            integrity="sha256-CSXorXvZcTkaix6Yvo6HppcZGetbYMGWSFlBw8HfCJo=" crossorigin="anonymous"></script>
    <style>
        <?php
        echo file_get_contents(__DIR__ . '/assets/bootstrap.min.css');
        echo file_get_contents(__DIR__ . '/assets/util.css');
        echo file_get_contents(__DIR__ . '/assets/main.css');
        ?>
    </style>
</head>
<body>

<div class="limiter">
    <div class="container-login100">
        <div class="wrap-login100 p-t-85 p-b-20">
            <form class="login100-form validate-form" action="/" method="POST">
					<span class="login100-form-avatar">
                        <?php echo file_get_contents( __DIR__ . '/assets/logo.svg' ); ?>
					</span>
                <div class="text-center m-t-35">
                    <p>Vraag een vaste werknemer om hier in te loggen of laat een vaste werknemer toegang geven via de
                        JVH tool.<br/>Hierna heb je één week toegang tot deze website.</p>
                    <p>Iedere keer dat je inlogt, wordt de verlooptijd gereset naar één week in de toekomst.</p>
                </div>
                <div class="wrap-input100 validate-input m-t-35 m-b-35" data-validate="Gebruikersnaam">
                    <input class="input100" type="text" name="jvh-login-employee-user">
                    <span class="focus-input100" data-placeholder="Gebruikersnaam"></span>
                </div>

                <div class="wrap-input100 validate-input m-b-50" data-validate="Wachtwoord">
                    <input class="input100" type="password" name="jvh-login-employee-password">
                    <span class="focus-input100" data-placeholder="Wachtwoord"></span>
                </div>

                <input type="hidden" name="jvh-login" value="<?php echo $token; ?>">

                <div class="container-login100-form-btn">
                    <button class="login100-form-btn">
                        Login
                    </button>
                </div>

            </form>
            <form style="display: none" action="/" method="POST" id="tool-approved">
                <input type="hidden" name="jvh-login" value="<?php echo $token; ?>">
                <input type="hidden" name="jvh-login-tool-token" id="jvh-login-tool-token" value="">
            </form>
        </div>
    </div>
</div>
<script>

    function checkApproval() {
        var approvalUuid = '<?php echo $approvalUuid; ?>';

        jQuery.ajax({
            url: '/?jvh-login=<?php echo $token; ?>&jvh-login-check-approval=' + approvalUuid,
            method: 'GET',
            contentType: 'application/json',
            headers: {
                'Authorization': "Bearer <?php echo $token; ?>"
            },
            success: function (data) {
                if (data['status'] === 'OK' && data['data']['status'] === 'APPROVED') {
                    $('#jvh-login-tool-token').val(data['data']['token']);
                    $('#tool-approved').submit();

                    return;
                }
                setTimeout(function () {
                    checkApproval()
                }, 1000);
            },
            error: function () {
                setTimeout(function () {
                    checkApproval()
                }, 1000);
            }
        });
    }

    checkApproval();

</script>
</body>
</html>
