<?php  
    if ($_SERVER['SERVER_NAME'] != 'streetscooter-cloud-system.eu')
        $_domain = '<span class="domain">' . $_SERVER['SERVER_NAME'] . '</span>';

        $locallist = array('127.0.0.1', "::1");
        if(!in_array($_SERVER['REMOTE_ADDR'], $locallist)) {
            $ssl = 'https://';
        } else {
            $ssl = 'http://';
        }
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <title>StreetScooter Cloud Systems</title>
    <meta name="description" content="">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" type="text/css" href="css/skeleton.css?5316">
    <link rel="stylesheet" type="text/css" href="css/errorpage.css">
</head>
<body class="logged-in">
<div class="pagewrap">
    <div class="container">
        <div class="row">
            <div class="six columns div4logo">
                <a href="<?php echo $ssl . $_SERVER['SERVER_NAME'] ?>">
                    <img src="images/Logo_StreetScooter_Long.svg" class="sts_logo" alt="StreetScooter">
                </a><?php echo $_domain ?>
            </div>
            <div class="six columns div4logo">
                <img src="images/dplogo.svg" class="dp_logo" alt="StreetScooter">
            </div>
        </div>

        <div class="row user_login_info">
            <div class="six columns" style="text-align: left; padding:6px 0; white-space: nowrap;">&nbsp;</div>
            <div class="six columns" style="text-align: right; padding:6px 0">&nbsp;</div>
        </div>
        <div class="error-msg">
            <p>HTTP: <span>404</span></p>
            <code><span>this_page</span>.<em>not_found</em> = true;</code>
            <code><span>if</span> (<b>you_spelt_it_wrong</b>) {<span>try_again()</span>;}</code>
            <code><span>else if (<b>we_screwed_up</b>)</span> {<em>alert</em>(<i>"We're really sorry about that."</i>); <span>window</span>.<em>location</em> = home;}</code>
        </div>
        <center><a class="btn-home" href="index.php">HOME</a></center>
    </div><!--container-->
</div><!--pagewrap-->
<script type="text/javascript" src="js/errorpage.js"></script>
</body>
</html>
