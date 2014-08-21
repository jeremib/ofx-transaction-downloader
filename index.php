<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
	<title>USAA Downloader</title>
	<!-- Latest compiled and minified CSS -->
	<link rel="stylesheet" href="//maxcdn.bootstrapcdn.com/bootstrap/3.2.0/css/bootstrap.min.css">
	
	<!-- Optional theme -->
	<link rel="stylesheet" href="//maxcdn.bootstrapcdn.com/bootstrap/3.2.0/css/bootstrap-theme.min.css">

	<link rel="stylesheet" href="style.css">
</head>
<body>
	<div class="container">

      <form class="form-signin" role="form" method="post" action="download.php">
        <h2 class="form-signin-heading">Enter USAA Information</h2>
        <input type="text" class="form-control" placeholder="USAA Member Number" name="login" required autofocus>
        <input type="password" class="form-control" placeholder="Pin Number" name="password" required>
<!--         <div class="checkbox">
          <label>
            <input type="checkbox" value="remember-me"> Remember me
          </label>
        </div> -->
        <button class="btn btn-lg btn-primary btn-block" type="submit">Download</button>
      </form>

    </div> <!-- /container -->
	<!-- jQuery (necessary for Bootstrap's JavaScript plugins) -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.1/jquery.min.js"></script>
	<!-- Latest compiled and minified JavaScript -->
	<script src="//maxcdn.bootstrapcdn.com/bootstrap/3.2.0/js/bootstrap.min.js"></script>
</body>
</html>
